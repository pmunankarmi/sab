<?php
defined( 'ABSPATH' ) || exit;
function sab_content_languages() {
    return function_exists( 'pll_languages_list' ) ? pll_languages_list( array( 'fields' => 'slug' ) ) : array();
}
function sab_register_options() {
    if ( ! function_exists( 'acf_add_options_page' ) ) return;
    acf_add_options_page( array( 'page_title' => 'SAB Theme Options', 'menu_title' => 'Theme Options', 'menu_slug' => 'sab-options', 'capability' => 'manage_options', 'redirect' => false, 'post_id' => 'sab_global', 'position' => 60, 'icon_url' => 'dashicons-admin-customizer' ) );
    foreach ( sab_content_languages() as $lang ) {
        acf_add_options_sub_page( array( 'page_title' => 'SAB Options — ' . strtoupper( $lang ), 'menu_title' => strtoupper( $lang ) . ' Content & Labels', 'parent_slug' => 'sab-options', 'menu_slug' => 'sab-options-' . $lang, 'post_id' => 'sab_' . $lang, 'capability' => 'manage_options' ) );
        $fields = json_decode( file_get_contents( SAB_CONTENT_DIR . 'seed/option-fields.json' ), true );
        foreach ( $fields as &$field ) {
            $field['key'] .= '_' . $lang;
            if ( ! empty( $field['sub_fields'] ) ) foreach ( $field['sub_fields'] as &$sub ) $sub['key'] .= '_' . $lang;
            unset( $sub );
        }
        unset( $field );
        acf_add_local_field_group( array( 'key' => 'group_sab_options_' . $lang, 'title' => 'SAB ' . strtoupper( $lang ), 'fields' => $fields, 'location' => array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'sab-options-' . $lang ) ) ) ) );
    }
}
add_action( 'acf/init', 'sab_register_options' );
add_action( 'sab_register_import_options', 'sab_register_options' );
// The ACF control is an adapter for the native setting; no second logo value is stored.
add_filter( 'acf/pre_load_value', function ( $value, $post_id, $field ) {
    return 'sab_primary_logo' === $field['name'] && 'sab_global' === $post_id ? (int) get_theme_mod( 'custom_logo', 0 ) : $value;
}, 10, 3 );
add_filter( 'acf/pre_update_value', function ( $check, $value, $post_id, $field ) {
    if ( 'sab_primary_logo' !== $field['name'] || 'sab_global' !== $post_id ) return $check;
    if ( ! current_user_can( 'edit_theme_options' ) ) return false;
    $id = absint( $value );
    if ( $id && ! wp_attachment_is_image( $id ) ) return false;
    if ( $id ) set_theme_mod( 'custom_logo', $id ); else remove_theme_mod( 'custom_logo' );
    return true;
}, 10, 4 );
