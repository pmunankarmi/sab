<?php
defined( 'ABSPATH' ) || exit;
function sab_lang() { return function_exists( 'pll_current_language' ) ? ( pll_current_language( 'slug' ) ?: 'en' ) : 'en'; }
function sab_field( $name, $id = false ) { return function_exists( 'get_field' ) ? get_field( $name, $id ) : get_post_meta( $id ?: get_the_ID(), $name, true ); }
function sab_option( $name, $shared = false ) { return function_exists( 'get_field' ) ? get_field( $name, $shared ? 'sab_global' : 'sab_' . sab_lang() ) : ''; }
function sab_label( $key ) { return (string) sab_option( 'sab_label_' . $key ); }
function sab_copy( $value ) { return wp_kses( (string) $value, array( 'em' => array(), 'strong' => array(), 'br' => array(), 'span' => array( 'class' => true ) ) ); }
function sab_media_url( $id ) { return $id ? esc_url_raw( wp_get_attachment_url( (int) $id ) ) : ''; }
function sab_records( $type ) {
    return get_posts( array( 'post_type' => $type, 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => array( 'menu_order' => 'ASC', 'ID' => 'ASC' ), 'lang' => sab_lang(), 'suppress_filters' => false ) );
}
function sab_milestones() { static $cache = array(); $lang = sab_lang(); if ( ! isset( $cache[$lang] ) ) $cache[$lang] = sab_records( 'sab_milestone' ); return $cache[$lang]; }
function sab_frontend_data() {
    $lang = sab_lang(); $items = array(); $media = array_fill_keys( array( 'IMGS', 'VIDS', 'VMASK', 'CUT', 'BARE' ), array() );
    foreach ( sab_milestones() as $i => $post ) {
        $items[] = array( 'year' => (int) sab_field( 'sab_year', $post->ID ), $lang => array( 'title' => esc_html( get_the_title( $post ) ), 'desc' => esc_html( wp_strip_all_tags( $post->post_content ) ) ), 'layout' => sab_field( 'sab_layout', $post->ID ), 'finale' => (bool) sab_field( 'sab_finale', $post->ID ) );
        $image = sab_media_url( get_post_thumbnail_id( $post ) );
        if ( $image ) $media['IMGS'][$i] = esc_attr( $image );
        foreach ( array( 'VIDS' => 'sab_video', 'VMASK' => 'sab_mask' ) as $key => $field ) {
            $url = sab_media_url( sab_field( $field, $post->ID ) ); if ( $url ) $media[$key][$i] = esc_attr( $url );
        }
        if ( sab_field( 'sab_cutout', $post->ID ) ) $media['CUT'][$i] = 1;
    }
    $labels = array();
    foreach ( array( 'play_century', 'pause_century', 'dark_label', 'light_label', 'film_sound_on', 'film_sound_off', 'close' ) as $key ) $labels[$key] = esc_attr( sab_label( $key ) );
    return array( 'lang' => $lang, 'items' => $items, 'media' => $media, 'labels' => $labels );
}
function sab_logo( $class = 'nav-logo', $footer = false ) {
    $id = (int) sab_option( $footer ? 'sab_footer_logo' : 'sab_language_logo' );
    if ( ! $id ) $id = (int) get_theme_mod( 'custom_logo' );
    if ( ! $id ) return;
    $dark = (int) sab_option( 'sab_language_dark_logo' ) ?: (int) sab_option( 'sab_dark_logo', true );
    echo '<span class="sab-logo-pair">';
    echo wp_get_attachment_image( $id, 'full', false, array( 'class' => $class . ' sab-logo-normal', 'loading' => 'eager', 'alt' => get_bloginfo( 'name' ) ) );
    if ( $dark && ! $footer ) echo wp_get_attachment_image( $dark, 'full', false, array( 'class' => $class . ' sab-logo-dark', 'loading' => 'eager', 'alt' => get_bloginfo( 'name' ) ) );
    echo '</span>';
}
function sab_language_switcher() {
    if ( ! function_exists( 'pll_the_languages' ) ) return;
    $languages = pll_the_languages( array( 'raw' => 1, 'hide_if_empty' => 0, 'hide_if_no_translation' => 1 ) );
    echo '<div class="lang-switch">';
    foreach ( $languages as $language ) {
        printf( '<a class="lang-btn%s" href="%s" lang="%s" hreflang="%s"%s>%s</a>', $language['current_lang'] ? ' active' : '', esc_url( $language['url'] ), esc_attr( $language['slug'] ), esc_attr( str_replace( '_', '-', $language['locale'] ) ), $language['current_lang'] ? ' aria-current="page"' : '', esc_html( $language['name'] ) );
    }
    echo '</div>';
}
function sab_video( $id, $poster, $element, $label, $loop = false ) {
    $url = sab_media_url( $id ); if ( ! $url ) {
        if ( $poster ) echo wp_get_attachment_image( $poster, 'full', false, array( 'class' => 'sab-video-poster' ) );
        return;
    }
    printf( '<video id="%s" src="%s"%s muted playsinline preload="%s" aria-label="%s"%s></video>', esc_attr( $element ), esc_url( $url ), $poster ? ' poster="' . esc_url( sab_media_url( $poster ) ) . '"' : '', $loop ? 'metadata' : 'none', esc_attr( $label ), $loop ? ' autoplay loop' : '' );
}
