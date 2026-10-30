<?php
defined( 'ABSPATH' ) || exit;
function sab_content_languages() {
    return function_exists( 'pll_languages_list' ) ? pll_languages_list( array( 'fields' => 'slug' ) ) : array();
}
function sab_string_fields() {
    $fields = json_decode( file_get_contents( SAB_CONTENT_DIR . 'seed/option-fields.json' ), true );
    return array_values( array_filter( $fields, static fn( $field ) => in_array( $field['type'], array( 'text', 'textarea' ), true ) ) );
}
function sab_register_options() {
    if ( ! function_exists( 'acf_add_options_page' ) ) return;
    acf_add_options_page( array( 'page_title' => 'SAB Theme Options', 'menu_title' => 'Theme Options', 'menu_slug' => 'sab-options', 'capability' => 'manage_options', 'redirect' => false, 'post_id' => 'sab_global', 'position' => 60, 'icon_url' => 'dashicons-admin-customizer' ) );
    $choices = array_combine( sab_content_languages(), sab_content_languages() );
    acf_add_local_field_group( array( 'key' => 'group_sab_logo_variants', 'title' => 'Language logo variants', 'fields' => array(
        array( 'key' => 'field_sab_logo_variants', 'name' => 'sab_logo_variants', 'label' => 'Logo variants', 'type' => 'repeater', 'layout' => 'row', 'button_label' => 'Add language logos', 'instructions' => 'Optional language-specific assets. Empty primary logo inherits the native Site Logo.', 'sub_fields' => array(
            array( 'key' => 'field_sab_variant_language', 'name' => 'language', 'label' => 'Language', 'type' => 'select', 'choices' => $choices ?: array(), 'required' => 1 ),
            array( 'key' => 'field_sab_variant_logo', 'name' => 'logo', 'label' => 'Primary logo override', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium' ),
            array( 'key' => 'field_sab_variant_dark', 'name' => 'dark_logo', 'label' => 'Dark-background logo', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium' ),
            array( 'key' => 'field_sab_variant_footer', 'name' => 'footer_logo', 'label' => 'Footer logo', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium' ),
        ) ),
    ), 'location' => array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'sab-options' ) ) ) ) );
    // Field aliases keep old theme versions working during a rolling plugin/theme update.
    // There are no language-specific options pages or visible groups.
    $legacy_fields = json_decode( file_get_contents( SAB_CONTENT_DIR . 'seed/option-fields.json' ), true );
    foreach ( sab_content_languages() as $lang ) {
        $aliases = array();
        foreach ( $legacy_fields as $field ) {
            if ( 'tab' === $field['type'] ) continue;
            $field['key'] .= '_' . $lang; $aliases[] = $field;
        }
        acf_add_local_field_group( array( 'key' => 'group_sab_legacy_aliases_' . $lang, 'title' => 'SAB compatibility aliases', 'fields' => $aliases, 'location' => array() ) );
    }
    $fields = sab_string_fields();
    foreach ( $fields as &$field ) $field['instructions'] = 'Source text. Translate under Languages → Translations, group SAB. Changing the source creates a new string to translate.';
    unset( $field );
    array_unshift( $fields, array( 'key' => 'field_sab_translation_help', 'name' => 'sab_translation_help', 'label' => 'Polylang translations', 'type' => 'message', 'message' => 'Manage translations in Languages → Translations (group SAB). These fields define the source strings; shared phone, email, links and logos stay in this options page.' ) );
    acf_add_local_field_group( array( 'key' => 'group_sab_strings', 'title' => 'Global source text and interface labels', 'fields' => $fields, 'location' => array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'sab-options' ) ) ) ) );
}
add_action( 'acf/init', 'sab_register_options' );
add_action( 'sab_register_import_options', 'sab_register_options' );
function sab_register_strings() {
    if ( ! function_exists( 'pll_register_string' ) || ! function_exists( 'get_field' ) ) return;
    foreach ( sab_string_fields() as $field ) {
        $source = (string) get_field( $field['name'], 'sab_global', false );
        if ( '' !== $source ) pll_register_string( $field['name'], $source, 'SAB', 'textarea' === $field['type'] );
    }
    foreach ( (array) get_field( 'sab_social', 'sab_global' ) as $row ) {
        if ( is_array( $row ) && ! empty( $row['sab_social_label'] ) ) pll_register_string( 'social_' . $row['sab_social_platform'], $row['sab_social_label'], 'SAB' );
    }
}
add_action( 'admin_init', 'sab_register_strings', 20 );
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

// Allow an older installed theme to read the new catalogue while it awaits its update.
add_filter( 'acf/pre_load_value', function ( $value, $post_id, $field ) {
    if ( ! get_option( 'sab_polylang_strings_migrated_v1' ) || ! str_starts_with( (string) $post_id, 'sab_' ) || 'sab_global' === $post_id ) return $value;
    $lang = substr( $post_id, 4 );
    if ( ! in_array( $lang, sab_content_languages(), true ) ) return $value;
    $keys = array( 'sab_language_logo' => 'logo', 'sab_language_dark_logo' => 'dark_logo', 'sab_footer_logo' => 'footer_logo' );
    if ( isset( $keys[$field['name']] ) ) {
        foreach ( (array) get_field( 'sab_logo_variants', 'sab_global' ) as $row ) {
            if ( is_array( $row ) && ( $row['language'] ?? '' ) === $lang ) return $row[$keys[$field['name']]] ?? 0;
        }
        return 0;
    }
    $names = array_column( sab_string_fields(), 'name' );
    if ( ! in_array( $field['name'], $names, true ) ) return $value;
    $source = (string) get_field( $field['name'], 'sab_global', false );
    return function_exists( 'pll_translate_string' ) ? pll_translate_string( $source, $lang ) : $source;
}, 20, 3 );
