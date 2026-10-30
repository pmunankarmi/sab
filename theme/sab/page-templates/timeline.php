<?php
/** Template Name: SAB Timeline */
defined( 'ABSPATH' ) || exit; get_header(); the_post(); ?>
<main id="main-content">
<header class="vhero"><div class="emblem" role="img" aria-label="<?php echo esc_attr( sab_label( 'hero_media_label' ) ); ?>"></div><h1><?php echo sab_copy( sab_field( 'sab_hero_title' ) ?: get_the_title() ); ?></h1><?php the_content(); ?></header>
<div class="vt" id="vt"><div class="vt-spine" id="vtSpine"><div class="vt-fill" id="vtFill"></div></div>
<?php foreach ( sab_milestones() as $milestone ) : ?><article><h3><?php echo esc_html( get_the_title( $milestone ) ); ?></h3><p><?php echo esc_html( $milestone->post_content ); ?></p></article><?php endforeach; ?>
</div>
<div class="lead-overlay" id="leadOverlay" hidden><div class="lead-chrome"><button class="lead-close" id="leadClose" type="button" aria-label="<?php echo esc_attr( sab_label( 'close' ) ); ?>">✕</button></div><?php get_template_part( 'template-parts/leadership' ); ?></div>
</main><?php get_footer(); ?>
