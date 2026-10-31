<?php
defined( 'ABSPATH' ) || exit;
/** Copy existing bilingual options into Polylang without deleting the original backup values. */
function sab_migrate_option_strings( $seed_values = null ) {
    if ( get_option( 'sab_polylang_strings_migrated_v1' ) || ! function_exists( 'update_field' ) || ! function_exists( 'PLL' ) || ! class_exists( 'PLL_MO' ) ) return false;
    $languages = sab_content_languages(); if ( ! $languages ) return false;
    $default = function_exists( 'pll_default_language' ) ? pll_default_language( 'slug' ) : 'en';
    $values = is_array( $seed_values ) ? $seed_values : array(); $has_values = false;
    if ( ! is_array( $seed_values ) ) {
        foreach ( $languages as $lang ) {
            foreach ( array_merge( sab_string_fields(), array( array( 'name' => 'sab_language_logo' ), array( 'name' => 'sab_language_dark_logo' ), array( 'name' => 'sab_footer_logo' ) ) ) as $field ) {
                $value = get_option( 'sab_' . $lang . '_' . $field['name'], null );
                if ( null !== $value ) { $values[$lang][$field['name']] = $value; $has_values = true; }
            }
        }
        if ( ! $has_values ) return false;
    }
    // Keep the migration snapshot for recovery; it is not used by the frontend.
    add_option( 'sab_polylang_options_backup_v1', $values, '', false );
    $catalogues = array();
    foreach ( $languages as $lang ) {
        $catalogues[$lang] = new PLL_MO();
        $catalogues[$lang]->import_from_db( PLL()->model->get_language( $lang ) );
    }
    foreach ( sab_string_fields() as $field ) {
        $name = $field['name']; $source = get_option( 'sab_global_' . $name, null );
        if ( null === $source ) {
            $source = (string) ( $values[$default][$name] ?? '' );
            update_field( $field['key'], $source, 'sab_global' );
        }
        if ( '' === (string) $source ) continue;
        foreach ( $languages as $lang ) {
            // Preserve translations already edited in Polylang, including deliberate empty entries.
            if ( isset( $catalogues[$lang]->entries[(string) $source] ) || ! isset( $values[$lang][$name] ) ) continue;
            $translated = (string) $values[$lang][$name];
            if ( '' !== $translated ) $catalogues[$lang]->add_entry( new Translation_Entry( array( 'singular' => (string) $source, 'translations' => array( $translated ) ) ) );
        }
    }
    foreach ( $catalogues as $lang => $mo ) $mo->export_to_db( PLL()->model->get_language( $lang ) );
    if ( ! get_option( 'sab_global_sab_logo_variants', false ) ) {
        $rows = array();
        foreach ( $languages as $lang ) {
            $legacy = $values[$lang] ?? array();
            $rows[] = array( 'field_sab_variant_language' => $lang, 'field_sab_variant_logo' => absint( $legacy['sab_language_logo'] ?? 0 ), 'field_sab_variant_dark' => absint( $legacy['sab_language_dark_logo'] ?? 0 ), 'field_sab_variant_footer' => absint( $legacy['sab_footer_logo'] ?? 0 ) );
        }
        update_field( 'field_sab_logo_variants', $rows, 'sab_global' );
    }
    update_option( 'sab_polylang_strings_migrated_v1', 1, false );
    return true;
}
add_action( 'admin_init', function () { sab_migrate_option_strings(); }, 10 );
if ( defined( 'WP_CLI' ) && WP_CLI ) WP_CLI::add_command( 'sab migrate-strings', function () {
    $changed = sab_migrate_option_strings(); sab_register_strings();
    WP_CLI::success( $changed ? 'Existing global copy and logo variants migrated to Polylang and the shared options page.' : 'No pending string migration.' );
} );
