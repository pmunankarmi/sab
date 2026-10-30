<?php
/** Native WordPress updates from the public SAB repository's release assets. */
defined( 'ABSPATH' ) || exit;
final class SAB_GitHub_Updates {
    const REPO = 'https://github.com/pmunankarmi/sab';
    const API = 'https://api.github.com/repos/pmunankarmi/sab/releases/latest';
    const CACHE = 'sab_github_release_v1';
    public static function init() {
        add_filter( 'update_themes_github.com', array( __CLASS__, 'theme_update' ), 10, 4 );
        add_filter( 'update_plugins_github.com', array( __CLASS__, 'plugin_update' ), 10, 4 );
        add_filter( 'plugins_api', array( __CLASS__, 'plugin_info' ), 10, 3 );
        add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
        add_action( 'admin_post_sab_check_github', array( __CLASS__, 'manual_check' ) );
        add_action( 'admin_init', array( __CLASS__, 'maybe_check' ) );
        add_action( 'admin_notices', array( __CLASS__, 'notice' ) );
        add_action( 'upgrader_process_complete', array( __CLASS__, 'clear_cache' ), 10, 2 );
    }
    public static function release() {
        $cached = get_site_transient( self::CACHE );
        if ( is_array( $cached ) ) return $cached;
        $response = wp_remote_get( self::API, array( 'timeout' => 10, 'redirection' => 2, 'headers' => array( 'Accept' => 'application/vnd.github+json', 'X-GitHub-Api-Version' => '2022-11-28' ) ) );
        $status = is_wp_error( $response ) ? 0 : wp_remote_retrieve_response_code( $response );
        if ( 200 !== $status ) {
            $message = 404 === $status ? 'No published GitHub release is available yet.' : 'GitHub could not be reached. Existing installed files were not changed.';
            if ( 403 === $status || 429 === $status ) $message = 'GitHub rate limit reached. Try again later.';
            $result = array( 'error' => $message );
        } else {
            $json = json_decode( wp_remote_retrieve_body( $response ), true );
            $version = ltrim( (string) ( $json['tag_name'] ?? '' ), 'v' );
            $assets = array();
            foreach ( (array) ( $json['assets'] ?? array() ) as $asset ) {
                $url = $asset['browser_download_url'] ?? '';
                // Only the expected repository and versioned release paths may supply executable packages.
                $prefix = self::REPO . '/releases/download/';
                if ( str_starts_with( $url, $prefix ) && 'https' === wp_parse_url( $url, PHP_URL_SCHEME ) ) $assets[$asset['name']] = esc_url_raw( $url );
            }
            if ( ! preg_match( '/^\d+\.\d+\.\d+$/', $version ) || ! empty( $json['draft'] ) || ! empty( $json['prerelease'] ) || empty( $assets['sab-theme.zip'] ) || empty( $assets['sab-content.zip'] ) ) {
                $result = array( 'error' => 'The latest release is missing valid SAB version metadata or installable packages.' );
            } else {
                $result = array( 'version' => $version, 'url' => self::REPO . '/releases/tag/v' . $version, 'theme' => $assets['sab-theme.zip'], 'plugin' => $assets['sab-content.zip'], 'published' => sanitize_text_field( $json['published_at'] ?? '' ) );
            }
        }
        set_site_transient( self::CACHE, $result, isset( $result['error'] ) ? 5 * MINUTE_IN_SECONDS : HOUR_IN_SECONDS );
        update_site_option( 'sab_github_last_check', time() );
        return $result;
    }
    public static function theme_update( $update, $data, $stylesheet, $locales ) {
        if ( 'sab' !== $stylesheet || self::REPO !== rtrim( $data['UpdateURI'] ?? '', '/' ) ) return $update;
        $release = self::release(); if ( isset( $release['error'] ) ) return $update;
        return array( 'theme' => 'sab', 'version' => $release['version'], 'url' => $release['url'], 'package' => $release['theme'], 'requires_php' => '8.0' );
    }
    public static function plugin_update( $update, $data, $file, $locales ) {
        if ( 'sab-content/sab-content.php' !== $file || self::REPO . '/sab-content' !== rtrim( $data['UpdateURI'] ?? '', '/' ) ) return $update;
        $release = self::release(); if ( isset( $release['error'] ) ) return $update;
        return array( 'slug' => 'sab-content', 'version' => $release['version'], 'url' => $release['url'], 'package' => $release['plugin'], 'requires_php' => '8.0' );
    }
    public static function plugin_info( $result, $action, $args ) {
        if ( 'plugin_information' !== $action || 'sab-content' !== ( $args->slug ?? '' ) ) return $result;
        $release = self::release(); if ( isset( $release['error'] ) ) return $result;
        return (object) array( 'name' => 'SAB Content', 'slug' => 'sab-content', 'version' => $release['version'], 'homepage' => self::REPO, 'requires_php' => '8.0', 'download_link' => $release['plugin'], 'sections' => array( 'description' => 'SAB content types, ACF options, Polylang shared slugs and GitHub updates.', 'changelog' => '<a href="' . esc_url( $release['url'] ) . '">View release notes on GitHub</a>' ) );
    }
    public static function maybe_check() {
        if ( ! current_user_can( 'update_themes' ) && ! current_user_can( 'update_plugins' ) ) return;
        if ( get_site_transient( 'sab_github_admin_checked' ) ) return;
        set_site_transient( 'sab_github_admin_checked', 1, HOUR_IN_SECONDS );
        self::release(); wp_update_themes(); wp_update_plugins();
    }
    public static function manual_check() {
        if ( ! current_user_can( 'update_themes' ) || ! current_user_can( 'update_plugins' ) ) wp_die( esc_html__( 'You cannot check updates for this site.', 'sab' ) );
        check_admin_referer( 'sab_check_github' );
        delete_site_transient( self::CACHE ); delete_site_transient( 'sab_github_admin_checked' );
        // Native "Check again" behavior: clear the last-checked timestamp to force a request now.
        foreach ( array( 'update_themes', 'update_plugins' ) as $key ) { $transient = get_site_transient( $key ); if ( is_object( $transient ) ) { $transient->last_checked = 0; set_site_transient( $key, $transient ); } }
        self::maybe_check();
        wp_safe_redirect( admin_url( 'tools.php?page=sab-github-updates&checked=1' ) ); exit;
    }
    public static function menu() { add_management_page( 'SAB GitHub Updates', 'SAB GitHub Updates', 'update_plugins', 'sab-github-updates', array( __CLASS__, 'screen' ) ); }
    public static function screen() {
        $release = self::release(); $theme = wp_get_theme( 'sab' );
        $plugin = get_file_data( SAB_CONTENT_DIR . 'sab-content.php', array( 'Version' => 'Version' ) );
        echo '<div class="wrap"><h1>SAB GitHub Updates</h1><p>Repository: <a href="' . esc_url( self::REPO ) . '">pmunankarmi/sab</a></p>';
        echo '<p>Installed theme: <strong>' . esc_html( $theme->get( 'Version' ) ) . '</strong> · Content plugin: <strong>' . esc_html( $plugin['Version'] ) . '</strong></p>';
        if ( isset( $release['error'] ) ) echo '<div class="notice notice-warning inline"><p>' . esc_html( $release['error'] ) . '</p></div>';
        else echo '<p>Latest release: <a href="' . esc_url( $release['url'] ) . '">' . esc_html( $release['version'] ) . '</a></p>';
        $checked = (int) get_site_option( 'sab_github_last_check', 0 );
        if ( $checked ) echo '<p>Last checked: ' . esc_html( wp_date( 'Y-m-d H:i:s', $checked ) ) . '</p>';
        echo '<p>Every push to main builds a release. Checks run hourly when an administrator visits and during normal WordPress update checks. Install available packages from Dashboard → Updates. GitHub pushes do not install files automatically.</p><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="sab_check_github">';
        wp_nonce_field( 'sab_check_github' ); submit_button( 'Check GitHub now', 'secondary' );
        echo '</form><p><a class="button button-primary" href="' . esc_url( admin_url( 'update-core.php' ) ) . '">Open WordPress Updates</a></p></div>';
    }
    public static function notice() {
        if ( ! current_user_can( 'update_plugins' ) ) return;
        $release = get_site_transient( self::CACHE ); if ( ! is_array( $release ) || empty( $release['version'] ) ) return;
        $theme = wp_get_theme( 'sab' ); $plugin = get_file_data( SAB_CONTENT_DIR . 'sab-content.php', array( 'Version' => 'Version' ) );
        if ( ! version_compare( $release['version'], $theme->get( 'Version' ), '>' ) && ! version_compare( $release['version'], $plugin['Version'], '>' ) ) return;
        printf( '<div class="notice notice-info"><p>%s <a href="%s">%s</a></p></div>', esc_html( 'SAB ' . $release['version'] . ' is available from GitHub.' ), esc_url( admin_url( 'update-core.php' ) ), esc_html__( 'Review updates', 'sab' ) );
    }
    public static function clear_cache( $upgrader, $options ) {
        $theme = in_array( 'sab', (array) ( $options['themes'] ?? array() ), true );
        $plugin = in_array( 'sab-content/sab-content.php', (array) ( $options['plugins'] ?? array() ), true );
        if ( $theme || $plugin ) { delete_site_transient( self::CACHE ); delete_site_transient( 'sab_github_admin_checked' ); }
    }
}
SAB_GitHub_Updates::init();
