<?php
defined( 'ABSPATH' ) || exit;
/** Explicit, repeatable seed migration. It never runs on activation or frontend requests. */
function sab_import_asset( $relative, $title = '' ) {
    $existing = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => 1, 'meta_key' => '_sab_source_asset', 'meta_value' => $relative, 'fields' => 'ids', 'suppress_filters' => true, 'lang' => '' ) );
    if ( $existing ) return (int) $existing[0];
    $base = realpath( SAB_CONTENT_DIR . 'seed/media' ); $source = realpath( $base . '/' . $relative );
    if ( ! $source || ! str_starts_with( $source, $base . DIRECTORY_SEPARATOR ) ) throw new RuntimeException( 'Missing seed asset: ' . $relative );
    require_once ABSPATH . 'wp-admin/includes/file.php'; require_once ABSPATH . 'wp-admin/includes/media.php'; require_once ABSPATH . 'wp-admin/includes/image.php';
    $temp = wp_tempnam( basename( $source ) ); copy( $source, $temp );
    $id = media_handle_sideload( array( 'name' => basename( $source ), 'tmp_name' => $temp ), 0, $title );
    if ( is_wp_error( $id ) ) throw new RuntimeException( $id->get_error_message() );
    update_post_meta( $id, '_sab_source_asset', $relative );
    if ( $title ) update_post_meta( $id, '_wp_attachment_image_alt', $title );
    return $id;
}
function sab_seed_post( $key, $lang, $data ) {
    $existing = get_posts( array( 'post_type' => $data['post_type'], 'post_status' => 'any', 'posts_per_page' => 1, 'meta_key' => '_sab_seed_key', 'meta_value' => $key . '_' . $lang, 'fields' => 'ids', 'suppress_filters' => true, 'lang' => '' ) );
    if ( $existing ) return array( (int) $existing[0], false );
    $id = wp_insert_post( wp_slash( array_merge( $data, array( 'post_status' => 'publish' ) ) ), true );
    if ( is_wp_error( $id ) ) throw new RuntimeException( $id->get_error_message() );
    update_post_meta( $id, '_sab_seed_key', $key . '_' . $lang ); pll_set_post_language( $id, $lang );
    // Re-run uniqueness once the language exists: shared-slug helper can now resolve it.
    wp_update_post( array( 'ID' => $id, 'post_name' => $data['post_name'] ) );
    return array( $id, true );
}
function sab_seed_field( $name, $value, $id ) { update_field( 'field_' . $name, $value, $id ); }
function sab_run_import() {
    if ( ! current_user_can( 'manage_options' ) && ! ( defined( 'WP_CLI' ) && WP_CLI ) ) throw new RuntimeException( 'Administrator access required.' );
    if ( ! function_exists( 'update_field' ) || ! function_exists( 'pll_set_post_language' ) ) throw new RuntimeException( 'Activate ACF Pro and Polylang first.' );
    $seed = json_decode( file_get_contents( SAB_CONTENT_DIR . 'seed/content.json' ), true, 512, JSON_THROW_ON_ERROR );
    if ( ! PLL()->model->get_language( 'en' ) || ! PLL()->model->get_language( 'ar' ) ) throw new RuntimeException( 'Run wp sab setup first, then wp sab import in a new request.' );
    // Register option schemas after creating the languages in this request.
    do_action( 'sab_register_import_options' );
    $pairs = array(); $page_ids = array(); $global_values = array();
    foreach ( $seed['items'] as $i => $item ) {
        foreach ( array( 'en', 'ar' ) as $lang ) {
            $text = $item[$lang];
            list( $id, $new ) = sab_seed_post( 'milestone_' . $i, $lang, array( 'post_type' => 'sab_milestone', 'post_title' => $text['title'], 'post_content' => $text['desc'], 'post_name' => 'milestone-' . str_pad( (string) $i, 2, '0', STR_PAD_LEFT ) . '-' . $item['year'], 'menu_order' => $i ) );
            $pairs['milestone_' . $i][$lang] = $id;
            if ( ! $new ) continue;
            $filename = str_pad( (string) $i, 2, '0', STR_PAD_LEFT ) . '-' . $item['year'];
            set_post_thumbnail( $id, sab_import_asset( 'milestone-img/' . $filename . '.webp' ) );
            sab_seed_field( 'sab_year', $item['year'], $id );
            if ( is_file( SAB_CONTENT_DIR . 'seed/media/milestone-vid/' . $filename . '.mp4' ) ) sab_seed_field( 'sab_video', sab_import_asset( 'milestone-vid/' . $filename . '.mp4' ), $id );
            if ( is_file( SAB_CONTENT_DIR . 'seed/media/milestone-cut/' . str_pad( (string) $i, 2, '0', STR_PAD_LEFT ) . '-mask.png' ) ) sab_seed_field( 'sab_mask', sab_import_asset( 'milestone-cut/' . str_pad( (string) $i, 2, '0', STR_PAD_LEFT ) . '-mask.png' ), $id );
            sab_seed_field( 'sab_cutout', in_array( $i, array( 4, 6, 21, 35 ), true ), $id );
            sab_seed_field( 'sab_finale', 35 === $i, $id );
        }
    }
    foreach ( array( 'en', 'ar' ) as $lang ) {
        $t = array_map( static fn( $v ) => html_entity_decode( $v, ENT_QUOTES | ENT_HTML5, 'UTF-8' ), $seed['translations'][$lang] );
        foreach ( array( 'home', 'timeline' ) as $view ) {
            list( $id, $new ) = sab_seed_post( $view, $lang, array( 'post_type' => 'page', 'post_title' => wp_strip_all_tags( $t['home' === $view ? 'hero_title' : 'v_title'] ), 'post_content' => $t['home' === $view ? 'intro_body_1' : 'v_sub'], 'post_name' => $view ) );
            $pairs[$view][$lang] = $id; $page_ids[$lang][$view] = $id;
            if ( $new ) {
                sab_seed_field( 'sab_hero_title', $t['home' === $view ? 'hero_title' : 'v_title'], $id );
                if ( 'timeline' === $view ) update_post_meta( $id, '_wp_page_template', 'page-templates/timeline.php' );
                else {
                    foreach ( array( 'sab_intro_title' => 'intro_title_1', 'sab_outro_title' => 'outro_title', 'sab_outro_body' => 'outro_body' ) as $field => $key ) sab_seed_field( $field, $t[$key], $id );
                    foreach ( array( 'sab_hero_video' => 'hero_loop.mp4', 'sab_hero_poster' => 'hero_loop_poster.webp', 'sab_film' => 'sab100.mp4', 'sab_film_poster' => 'sab100_poster.webp' ) as $field => $file ) sab_seed_field( $field, sab_import_asset( 'brand/' . $file ), $id );
                }
            }
        }
        foreach ( array( 'chair', 'ceo' ) as $i => $role ) {
            list( $id, $new ) = sab_seed_post( $role, $lang, array( 'post_type' => 'sab_leader', 'post_name' => $role, 'post_title' => $t['leader_role_' . $role], 'post_content' => implode( "\n\n", array_map( static fn( $key ) => $t[$key], array_values( array_filter( array_keys( $t ), static fn( $key ) => preg_match( '/^' . $role . '_p[0-9]+$/', $key ) ) ) ) ), 'menu_order' => $i ) );
            $pairs[$role][$lang] = $id;
            if ( $new ) { set_post_thumbnail( $id, sab_import_asset( 'leader-' . $i . '.jpg' ) ); sab_seed_field( 'sab_pull_quote', $t[$role . '_pull'], $id ); }
        }
        if ( ! get_option( 'sab_polylang_strings_migrated_v1' ) ) {
            $fields = json_decode( file_get_contents( SAB_CONTENT_DIR . 'seed/option-fields.json' ), true );
            $values = array( 'sab_established' => $t['outro_meta_2'], 'sab_copyright' => $t['footer_rights'], 'sab_legal_text' => $t['footer_entity'], 'sab_website_label' => 'www.sab.com', 'sab_contact_label' => 'ar' === $lang ? 'معلومات الاتصال' : 'Contact details' );
            if ( 'ar' === $lang ) $values['sab_language_logo'] = sab_import_asset( 'brand/lockup_ar_light.png' );
            $values['sab_language_dark_logo'] = sab_import_asset( 'brand/lockup_' . $lang . '_dark.png' );
            $values['sab_footer_logo'] = sab_import_asset( 'brand/lockup_' . $lang . '_dark.png' );
            foreach ( $fields as $field ) {
                $name = $field['name']; if ( 'tab' === $field['type'] ) continue;
                $key = str_replace( 'sab_label_', '', $name );
                if ( isset( $values[$name] ) || isset( $t[$key] ) ) $global_values[$lang][$name] = $values[$name] ?? $t[$key];
            }
        }
    }
    if ( ! get_option( 'sab_polylang_strings_migrated_v1' ) ) {
        if ( ! sab_migrate_option_strings() && $global_values ) sab_migrate_option_strings( $global_values );
    }
    foreach ( $pairs as $pair ) pll_save_post_translations( $pair );
    if ( ! get_option( 'sab_import_configured' ) ) {
        update_option( 'show_on_front', 'page' ); update_option( 'page_on_front', $page_ids['en']['home'] ); update_option( 'blogname', 'SAB' ); update_option( 'blogdescription', '' );
        set_theme_mod( 'custom_logo', sab_import_asset( 'brand/lockup_en_light.png' ) );
        update_option( 'site_icon', sab_import_asset( 'favicon.png' ) );
        sab_seed_field( 'sab_dark_logo', sab_import_asset( 'brand/lockup_en_dark.png' ), 'sab_global' );
        sab_seed_field( 'sab_emblem', sab_import_asset( 'brand/coin_emblem.png' ), 'sab_global' );
        sab_seed_field( 'sab_phone', '+966 11 4050677', 'sab_global' ); sab_seed_field( 'sab_website', 'https://www.sab.com/', 'sab_global' );
        $social = array(); foreach ( array( 'x' => array( 'X (Twitter)', 'https://twitter.com/alawwalsab/' ), 'linkedin' => array( 'LinkedIn', 'https://www.linkedin.com/company/alawwalsab/' ), 'instagram' => array( 'Instagram', 'https://instagram.com/alawwalsab/' ), 'facebook' => array( 'Facebook', 'https://www.facebook.com/alawwalsab/' ) ) as $platform => $details ) $social[] = array( 'field_sab_social_platform' => $platform, 'field_sab_social_label' => $details[0], 'field_sab_social_url' => $details[1] );
        sab_seed_field( 'sab_social', $social, 'sab_global' );
        $locations = get_theme_mod( 'nav_menu_locations', array() ); $menu_pairs = array();
        foreach ( array( 'en', 'ar' ) as $lang ) {
            $t = $seed['translations'][$lang];
            foreach ( array( 'primary', 'footer' ) as $location ) {
                $menu = wp_create_nav_menu( 'SAB ' . ucfirst( $location ) . ' ' . strtoupper( $lang ) );
                if ( is_wp_error( $menu ) ) throw new RuntimeException( $menu->get_error_message() );
                pll_set_term_language( $menu, $lang ); $menu_pairs[$location][$lang] = $menu;
                if ( 'en' === $lang ) $locations[$location] = $menu;
                if ( 'primary' === $location ) {
                    wp_update_nav_menu_item( $menu, 0, array( 'menu-item-object-id' => $page_ids[$lang]['timeline'], 'menu-item-object' => 'page', 'menu-item-type' => 'post_type', 'menu-item-title' => $t['nav_vertical'], 'menu-item-status' => 'publish', 'menu-item-classes' => 'sab-view-link' ) );
                    wp_update_nav_menu_item( $menu, 0, array( 'menu-item-url' => '#leadership', 'menu-item-title' => $t['nav_leadership'], 'menu-item-classes' => 'sab-leadership', 'menu-item-status' => 'publish' ) );
                } else {
                    foreach ( array( 'footer_disclaimer' => 'privacy-and-security', 'footer_terms' => 'term-and-conditions', 'footer_privacy' => 'data-privacy-notice' ) as $key => $path ) wp_update_nav_menu_item( $menu, 0, array( 'menu-item-url' => 'https://www.sab.com/' . $lang . '/personal/' . $path . '/', 'menu-item-title' => html_entity_decode( $t[$key] ), 'menu-item-status' => 'publish' ) );
                }
            }
        }
        foreach ( $menu_pairs as $pair ) pll_save_term_translations( $pair );
        set_theme_mod( 'nav_menu_locations', $locations );
        $pll_options = get_option( 'polylang', array() );
        $pll_options['nav_menus'][get_stylesheet()] = $menu_pairs;
        update_option( 'polylang', $pll_options );
        update_option( 'sab_import_configured', 1 );
    }
    flush_rewrite_rules();
    return 'Imported 36 bilingual milestone pairs, two leadership pairs, pages, media, menus and options. Existing imported content was preserved.';
}
if ( defined( 'WP_CLI' ) && WP_CLI ) WP_CLI::add_command( 'sab import', function () { WP_CLI::success( sab_run_import() ); } );

if ( defined( 'WP_CLI' ) && WP_CLI ) WP_CLI::add_command( 'sab setup', function () {
    $options = get_option( 'polylang', array() );
    if ( ! class_exists( 'PLL_Admin_Model' ) ) require_once POLYLANG_DIR . '/admin/admin-model.php';
    $options['default_lang'] = $options['default_lang'] ?? 'en';
    PLL()->options['default_lang'] = $options['default_lang'];
    $model = new PLL_Admin_Model( $options );
    foreach ( array( 'en' => array( 'name' => 'English', 'locale' => 'en_US', 'rtl' => 0 ), 'ar' => array( 'name' => 'العربية', 'locale' => 'ar', 'rtl' => 1 ) ) as $slug => $language ) {
        if ( ! $model->get_language( $slug ) ) {
            $result = $model->add_language( array_merge( $language, array( 'slug' => $slug, 'term_group' => 'en' === $slug ? 0 : 1 ) ) );
            if ( is_wp_error( $result ) ) throw new RuntimeException( $result->get_error_message() );
        }
    }
    $options = get_option( 'polylang', array() );
    if ( ! get_option( 'sab_import_configured' ) ) {
        $options['default_lang'] = 'en'; $options['force_lang'] = 1; $options['hide_default'] = 0; $options['rewrite'] = 1;
        $options['post_types'] = array_values( array_unique( array_merge( $options['post_types'] ?? array(), array( 'sab_milestone', 'sab_leader' ) ) ) );
        update_option( 'polylang', $options ); update_option( 'permalink_structure', '/%postname%/' );
    }
    WP_CLI::success( 'English, Arabic and shared-slug URL settings configured. Run wp sab import next.' );
} );
