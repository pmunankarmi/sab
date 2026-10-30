<?php
defined( 'ABSPATH' ) || exit;
require_once get_template_directory() . '/inc/content.php';
add_action( 'after_setup_theme', function () {
    load_theme_textdomain( 'sab', get_template_directory() . '/languages' );
    add_theme_support( 'title-tag' ); add_theme_support( 'post-thumbnails' );
    add_theme_support( 'custom-logo', array( 'flex-width' => true, 'flex-height' => true ) );
    add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
    register_nav_menus( array( 'primary' => __( 'Primary navigation', 'sab' ), 'footer' => __( 'Footer legal links', 'sab' ) ) );
} );
add_action( 'wp_head', function () { echo '<script>document.documentElement.classList.add("sab-js")</script>'; }, 1 );
add_action( 'wp_enqueue_scripts', function () {
    $version = wp_get_theme( 'sab' )->get( 'Version' );
    $base = get_template_directory_uri(); $vertical = is_page_template( 'page-templates/timeline.php' );
    $view = $vertical ? 'vertical' : 'index';
    wp_enqueue_style( 'sab-view', $base . '/assets/css/' . $view . '.css', array(), $version );
    wp_enqueue_style( 'sab-wp', $base . '/assets/css/wordpress.css', array( 'sab-view' ), $version );
    if ( is_front_page() || $vertical ) {
        wp_enqueue_script( 'sab-view', $base . '/assets/js/' . $view . '.js', array(), $version, true );
        wp_add_inline_script( 'sab-view', 'window.SAB=' . wp_json_encode( sab_frontend_data(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) . ';', 'before' );
    }
    wp_enqueue_script( 'sab-ui', $base . '/assets/js/ui.js', array(), $version, true );
    $poster = sab_field( 'sab_hero_poster' );
    if ( $poster ) wp_add_inline_style( 'sab-wp', ':root{--sab-hero-poster:url("' . esc_url( wp_get_attachment_url( $poster ) ) . '")}' );
    $emblem = sab_option( 'sab_emblem', true );
    if ( $emblem ) wp_add_inline_style( 'sab-wp', ':root{--sab-emblem:url("' . esc_url( wp_get_attachment_url( $emblem ) ) . '")}' );
} );
add_filter( 'nav_menu_link_attributes', function ( $atts, $item, $args ) {
    if ( 'primary' === $args->theme_location ) {
        $atts['class'] = 'nav-link';
        if ( in_array( 'sab-leadership', (array) $item->classes, true ) ) $atts['class'] .= ' lead-nav-link lead-open';
    }
    return $atts;
}, 10, 3 );

add_filter( 'wp_nav_menu_objects', function ( $items, $args ) {
    if ( 'primary' !== $args->theme_location || ! is_page_template( 'page-templates/timeline.php' ) ) return $items;
    foreach ( $items as $item ) if ( in_array( 'sab-view-link', (array) $item->classes, true ) ) {
        $item->url = function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' );
        $item->title = sab_label( 'nav_cinematic' );
    }
    return $items;
}, 10, 2 );
