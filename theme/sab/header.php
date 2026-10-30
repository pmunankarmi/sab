<?php defined( 'ABSPATH' ) || exit; ?>
<!doctype html>
<html <?php language_attributes(); ?> data-bs-theme="light">
<head><meta charset="<?php bloginfo( 'charset' ); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"><?php wp_head(); ?></head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main-content"><?php echo esc_html( sab_label( 'skip_content' ) ); ?></a>
<div class="curtain" id="curtain" aria-hidden="true"><div class="curtain-stage"><?php sab_logo( 'curtain-logo' ); ?><div class="bar"><div class="bar-fill" id="loadBar"></div></div><div class="load-label" id="loadLabel"><?php echo esc_html( sab_label( 'load_label' ) ); ?></div></div></div>
<nav class="navbar navbar-expand-lg fixed-top sab-nav" id="nav">
 <div class="container-fluid px-4 px-md-5">
  <a class="navbar-brand" href="<?php echo esc_url( function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' ) ); ?>"><?php sab_logo(); ?></a>
  <button class="navbar-toggler" type="button" aria-controls="mainNav" aria-expanded="false" aria-label="<?php echo esc_attr( sab_label( 'toggle_navigation' ) ); ?>"><span aria-hidden="true">☰</span></button>
  <div class="navbar-collapse justify-content-end" id="mainNav">
   <?php wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'menu_class' => 'sab-menu', 'fallback_cb' => false, 'depth' => 2 ) ); sab_language_switcher(); ?>
  </div>
 </div>
</nav>
