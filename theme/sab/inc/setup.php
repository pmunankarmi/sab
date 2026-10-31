<?php
/** Content and settings belong to the SAB theme; database identifiers stay unchanged. */
defined( 'ABSPATH' ) || exit;
// On the first request after upgrading an existing site, the old plugin may
// already have loaded. Let it finish this request, deactivate it, then let the
// theme own these hooks on the next request without duplicate declarations.
add_action( 'admin_init', function () {
    if ( is_plugin_active( 'sab-content/sab-content.php' ) ) {
        deactivate_plugins( 'sab-content/sab-content.php', true );
    }
}, 1 );
if ( defined( 'SAB_CONTENT_DIR' ) ) return;
define( 'SAB_THEME_DATA_DIR', get_template_directory() . '/' );
add_action( 'init', function () {
    foreach ( array( 'sab_milestone' => 'Milestones', 'sab_leader' => 'Leadership' ) as $type => $label ) {
        register_post_type( $type, array(
            'label' => __( $label, 'sab' ), 'public' => false, 'show_ui' => true, 'show_in_rest' => true,
            'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes', 'revisions', 'custom-fields' ),
            'menu_icon' => 'sab_milestone' === $type ? 'dashicons-backup' : 'dashicons-groups',
            'capability_type' => 'post', 'map_meta_cap' => true,
        ) );
    }
} );
add_filter( 'pll_get_post_types', function ( $types ) {
    $types['sab_milestone'] = 'sab_milestone'; $types['sab_leader'] = 'sab_leader'; return $types;
} );
// Shared fields only. Translated title, editor, excerpt and alt text stay independent.
add_filter( 'pll_copy_post_metas', function ( $metas, $sync ) {
    if ( $sync ) {
        $metas = array_diff( $metas, array( 'sab_hero_title', 'sab_outro_title', 'sab_outro_body', 'sab_pull_quote' ) );
        foreach ( array( 'sab_year', 'sab_video', 'sab_mask', 'sab_cutout', 'sab_finale', 'sab_layout' ) as $name ) {
            $metas[] = $name; $metas[] = '_' . $name;
        }
    }
    return array_unique( $metas );
}, 10, 2 );
add_action( 'after_setup_theme', function () {
    if ( function_exists( 'pll_current_language' ) && ! defined( 'POLYLANG_PRO' ) && ! function_exists( 'polylang_slug_unique_slug_in_language' ) ) {
        require_once SAB_THEME_DATA_DIR . 'includes/polylang-slug.php';
    }
}, 30 );
add_filter( 'acf/settings/load_json', function ( $paths ) { $paths[] = SAB_THEME_DATA_DIR . 'acf-json'; return $paths; } );
require_once SAB_THEME_DATA_DIR . 'includes/options.php';
require_once SAB_THEME_DATA_DIR . 'includes/string-migration.php';
require_once SAB_THEME_DATA_DIR . 'includes/github-updates.php';
require_once SAB_THEME_DATA_DIR . 'includes/import.php';
add_action( 'admin_notices', function () {
    if ( ! function_exists( 'acf_add_options_page' ) || ! function_exists( 'pll_current_language' ) ) {
        echo '<div class="notice notice-warning"><p>' . esc_html__( 'SAB requires ACF Pro and Polylang. Activate both before importing content.', 'sab' ) . '</p></div>';
    }
} );
