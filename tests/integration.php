<?php
/** Run with: wp eval-file tests/integration.php */
function sab_test( $condition, $message ) { if ( ! $condition ) throw new RuntimeException( $message ); echo 'PASS ' . $message . "\n"; }
wp_set_current_user( get_user_by( 'login', 'sabadmin' )->ID );
sab_test( function_exists( 'polylang_slug_unique_slug_in_language' ) && has_filter( 'wp_unique_post_slug', 'polylang_slug_unique_slug_in_language' ), 'Shared-slug helper registered' );
foreach ( array( 'en', 'ar' ) as $lang ) {
 foreach ( array( 'sab_milestone' => 36, 'sab_leader' => 2 ) as $type => $expected ) {
  $records = get_posts( array( 'post_type' => $type, 'posts_per_page' => -1, 'post_status' => 'publish', 'lang' => $lang, 'suppress_filters' => false, 'orderby' => 'menu_order', 'order' => 'ASC' ) );
  sab_test( count( $records ) === $expected, "$lang $type record count" );
  foreach ( $records as $post ) {
   $translation = pll_get_post( $post->ID, 'en' === $lang ? 'ar' : 'en' );
   sab_test( $translation && get_post_field( 'post_name', $translation ) === $post->post_name, 'Translation pair shares slug: ' . $post->post_name );
   sab_test( has_post_thumbnail( $post ), 'Featured image present: ' . $post->post_name );
  }
 }
}
$original = (int) get_theme_mod( 'custom_logo' );
$other = (int) get_field( 'sab_dark_logo', 'sab_global', false );
try {
 update_field( 'field_sab_primary_logo', $other, 'sab_global' );
 sab_test( (int) get_theme_mod( 'custom_logo' ) === $other, 'Theme Options logo updates native custom_logo' );
 set_theme_mod( 'custom_logo', $original ); acf_flush_value_cache( 'sab_global', 'sab_primary_logo' );
 sab_test( (int) get_field( 'sab_primary_logo', 'sab_global', false ) === $original, 'Native logo updates Theme Options field' );
 update_field( 'field_sab_primary_logo', false, 'sab_global' );
 sab_test( ! get_theme_mod( 'custom_logo' ), 'Removing Theme Options logo removes native logo' );
 set_theme_mod( 'custom_logo', $other ); remove_theme_mod( 'custom_logo' ); acf_flush_value_cache( 'sab_global', 'sab_primary_logo' );
 sab_test( ! get_field( 'sab_primary_logo', 'sab_global', false ), 'Removing native logo clears Theme Options control' );
} finally { set_theme_mod( 'custom_logo', $original ); acf_flush_value_cache( 'sab_global', 'sab_primary_logo' ); }
sab_test( false === get_option( 'sab_global_sab_primary_logo' ), 'Logo field has no duplicate option storage' );
$before = array_map( static fn( $t ) => (int) wp_count_posts( $t )->publish, array( 'sab_milestone', 'sab_leader', 'page' ) );
$attachments = (int) wp_count_posts( 'attachment' )->inherit;
sab_run_import();
$after = array_map( static fn( $t ) => (int) wp_count_posts( $t )->publish, array( 'sab_milestone', 'sab_leader', 'page' ) );
sab_test( $before === $after && $attachments === (int) wp_count_posts( 'attachment' )->inherit, 'Reimport creates no duplicate content or media' );
foreach ( array( '/en/home/', '/ar/home/', '/en/timeline/', '/ar/timeline/' ) as $path ) {
 $response = wp_remote_get( home_url( $path ), array( 'timeout' => 30, 'redirection' => 3 ) );
 sab_test( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ), 'HTTP 200 ' . $path );
 $html = wp_remote_retrieve_body( $response );
 sab_test( ! str_contains( $html, 'Fatal error' ) && ! str_contains( $html, 'Warning:' ), 'No PHP errors in ' . $path );
 sab_test( str_contains( $html, 'TIMELINE VIEW' ) || str_contains( $html, 'Timeline view' ) || str_contains( $html, 'Cinematic view' ) || str_contains( $html, 'العرض' ), 'Translated navigation rendered ' . $path );
 sab_test( str_contains( $html, '"items":[' ) && str_contains( $html, '"finale":true' ), 'Dynamic milestone data present ' . $path );
 if ( str_starts_with( $path, '/ar/' ) ) sab_test( str_contains( $html, 'dir="rtl"' ), 'Arabic document direction ' . $path );
}
echo "Integration suite complete.\n";
