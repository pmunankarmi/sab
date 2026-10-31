<?php
/** Native WordPress updates from the public SAB repository's release assets. */
defined( 'ABSPATH' ) || exit;
final class SAB_GitHub_Updates {
    const REPO = 'https://github.com/pmunankarmi/sab';
    const API = 'https://api.github.com/repos/pmunankarmi/sab/releases/latest';
    const CACHE = 'sab_github_release_v1';
    public static function init() {
        add_filter( 'update_themes_github.com', array( __CLASS__, 'theme_update' ), 10, 4 );
        add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
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
            if ( ! preg_match( '/^\d+\.\d+\.\d+$/', $version ) || ! empty( $json['draft'] ) || ! empty( $json['prerelease'] ) || empty( $assets['sab-theme.zip'] ) ) {
                $result = array( 'error' => 'The latest release is missing valid SAB version metadata or installable packages.' );
            } else {
                $result = array( 'version' => $version, 'url' => self::REPO . '/releases/tag/v' . $version, 'theme' => $assets['sab-theme.zip'], 'published' => sanitize_text_field( $json['published_at'] ?? '' ) );
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
    public static function maybe_check() {
        if ( ! current_user_can( 'update_themes' ) ) return;
        if ( get_site_transient( 'sab_github_admin_checked' ) ) return;
        set_site_transient( 'sab_github_admin_checked', 1, HOUR_IN_SECONDS );
        self::release(); wp_update_themes();
    }
    public static function menu() { add_management_page( 'SAB GitHub Updates', 'SAB GitHub Updates', 'update_themes', 'sab-github-updates', array( __CLASS__, 'screen' ) ); }
    public static function screen() {
        $release = self::release(); $theme = wp_get_theme( 'sab' );
        echo '<div class="wrap"><h1>SAB GitHub Updates</h1><p>Repository: <a href="' . esc_url( self::REPO ) . '">pmunankarmi/sab</a></p>';
        echo '<p>Installed theme: <strong>' . esc_html( $theme->get( 'Version' ) ) . '</strong></p>';
        if ( isset( $release['error'] ) ) echo '<div class="notice notice-warning inline"><p>' . esc_html( $release['error'] ) . '</p></div>';
        else echo '<p>Latest release: <a href="' . esc_url( $release['url'] ) . '">' . esc_html( $release['version'] ) . '</a></p>';
        $checked = (int) get_site_option( 'sab_github_last_check', 0 );
        if ( $checked ) echo '<p>Last checked: ' . esc_html( wp_date( 'Y-m-d H:i:s', $checked ) ) . '</p>';
        echo '<p>Every push to main builds a release. Update checks run automatically on administrator visits and during normal WordPress scheduled checks. Available packages appear in Dashboard → Updates. GitHub pushes do not install files automatically.</p>';
        echo '<p><a class="button button-primary" href="' . esc_url( admin_url( 'update-core.php' ) ) . '">Open WordPress Updates</a></p></div>';
    }
    public static function notice() {
        if ( ! current_user_can( 'update_themes' ) ) return;
        $release = get_site_transient( self::CACHE ); if ( ! is_array( $release ) || empty( $release['version'] ) ) return;
        $theme = wp_get_theme( 'sab' );
        if ( ! version_compare( $release['version'], $theme->get( 'Version' ), '>' ) ) return;
        printf( '<div class="notice notice-info"><p>%s <a href="%s">%s</a></p></div>', esc_html( 'SAB ' . $release['version'] . ' is available from GitHub.' ), esc_url( admin_url( 'update-core.php' ) ), esc_html__( 'Review updates', 'sab' ) );
    }
    public static function clear_cache( $upgrader, $options ) {
        $theme = in_array( 'sab', (array) ( $options['themes'] ?? array() ), true );
        $plugin = in_array( 'sab-content/sab-content.php', (array) ( $options['plugins'] ?? array() ), true );
        if ( $theme || $plugin ) { delete_site_transient( self::CACHE ); delete_site_transient( 'sab_github_admin_checked' ); }
    }
}
SAB_GitHub_Updates::init();
