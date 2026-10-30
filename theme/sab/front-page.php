<?php defined( 'ABSPATH' ) || exit; get_header(); the_post(); $milestones = sab_milestones(); $first = $milestones ? sab_field( 'sab_year', $milestones[0]->ID ) : ''; $last = $milestones ? sab_field( 'sab_year', end( $milestones )->ID ) : ''; ?>
<main id="main-content">
<section class="hero d-flex align-items-center justify-content-center">
 <div class="bg-num" id="heroBg" data-total="<?php echo esc_attr( $last && $first ? (int) $last - (int) $first : 0 ); ?>" aria-hidden="true"><?php echo esc_html( $last && $first ? (int) $last - (int) $first : '' ); ?></div>
 <div class="container position-relative" style="z-index:2"><div class="row justify-content-center"><div class="col-12 col-lg-10 text-center">
  <div class="hero-kicker"><?php echo esc_html( sab_label( 'hero_kicker' ) ); ?></div>
  <div class="hero-banner"><?php sab_video( sab_field( 'sab_hero_video' ), sab_field( 'sab_hero_poster' ), 'heroLoop', sab_label( 'hero_media_label' ), true ); ?></div>
  <h1 class="mb-0"><?php echo sab_copy( sab_field( 'sab_hero_title' ) ?: get_the_title() ); ?></h1><div class="hero-rule" aria-hidden="true"></div>
 </div></div></div>
 <div class="scroll-cue position-absolute start-50 bottom-0 translate-middle-x text-center mb-3"><?php echo esc_html( sab_label( 'scroll_cue' ) ); ?></div>
</section>
<?php if ( sab_field( 'sab_film' ) ) : ?>
<section class="film-section" id="filmSection"><div class="film-stage"><div class="film-frame" id="filmFrame">
 <?php sab_video( sab_field( 'sab_film' ), sab_field( 'sab_film_poster' ), 'centenaryFilm', sab_label( 'film_label' ) ); ?>
 <button class="film-sound" id="filmSound" type="button" aria-pressed="false"><span data-i18n="film_sound_on"><?php echo esc_html( sab_label( 'film_sound_on' ) ); ?></span></button>
 <button class="film-skip" id="filmSkip" type="button"><span><?php echo esc_html( sab_label( 'film_skip' ) ); ?></span></button>
 <div class="film-progress" aria-hidden="true"><span id="filmBar"></span></div>
</div></div><div class="film-cue"><p><?php echo esc_html( sab_label( 'film_cue' ) ); ?></p><span class="film-cue-arrow" aria-hidden="true"></span></div></section>
<?php endif; ?>
<?php if ( get_the_content() || sab_field( 'sab_intro_title' ) ) : ?>
<section class="intro-section" id="introSection"><div class="sadu-corner" aria-hidden="true"></div><div class="intro-statement">
 <h2><?php echo sab_copy( sab_field( 'sab_intro_title' ) ); ?></h2><?php the_content(); ?></div>
 <div class="span-line" aria-hidden="true"><span class="sy sy-a"><?php echo esc_html( $first ); ?></span><div class="sl-track"><i class="sl-base"></i><i class="sl-draw"></i><?php for ( $i = 4; $i < 100; $i += 10 ) : ?><span class="sl-tick" style="--x:<?php echo (int) $i; ?>%;--d:0.5s"></span><?php endfor; ?><div class="sl-mark"><b></b><span><?php echo esc_html( sab_label( 'span_label' ) ); ?></span></div></div><span class="sy sy-b"><?php echo esc_html( $last ); ?></span></div>
</section>
<?php endif; ?>
<?php get_template_part( 'template-parts/leadership' ); ?>
<div class="timeline<?php echo ! $milestones ? ' sab-empty' : ''; ?>" id="timeline" style="--sab-milestones:<?php echo count( $milestones ); ?>"><div class="stage" id="stage">
 <div class="era-year" id="eraYear" aria-hidden="true"><?php echo esc_html( $first ); ?></div>
 <div class="index-rail"><span><?php echo esc_html( sab_label( 'milestone_label' ) ); ?></span><span class="total"><b id="frameNum"><?php echo $milestones ? '01' : '00'; ?></b> / <?php echo count( $milestones ); ?></span></div>
 <div class="year-rail" id="yearRail"><span class="now" id="nowYear"><?php echo esc_html( $first ); ?></span><div class="track"><div class="track-fill"></div><div class="track-dot"></div></div></div>
 <div class="dot-rail d-flex gap-1" id="dotRail"></div>
 <div class="scrub" id="scrub" aria-label="<?php echo esc_attr( sab_label( 'timeline_nav' ) ); ?>">
  <button class="scrub-btn" id="scrubPrev" type="button" aria-label="<?php echo esc_attr( sab_label( 'previous' ) ); ?>"><b aria-hidden="true">‹</b></button><span class="scrub-y"><?php echo esc_html( $first ); ?></span><div class="scrub-track" id="scrubTrack"></div><span class="scrub-y"><?php echo esc_html( $last ); ?></span><button class="scrub-btn" id="scrubNext" type="button" aria-label="<?php echo esc_attr( sab_label( 'next' ) ); ?>"><b aria-hidden="true">›</b></button><button class="scrub-end" id="scrubEnd" type="button"><span><?php echo esc_html( sab_label( 'scrub_end' ) ); ?></span><b aria-hidden="true">⇥</b></button>
 </div><div class="items-wrap" id="itemsWrap"><?php foreach ( $milestones as $milestone ) : ?><article><h3><?php echo esc_html( get_the_title( $milestone ) ); ?></h3><p><?php echo esc_html( $milestone->post_content ); ?></p><?php echo get_the_post_thumbnail( $milestone, 'large' ); ?></article><?php endforeach; ?></div>
</div></div>
<button class="dark-toggle" id="darkToggle" type="button" aria-pressed="false"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3a9 9 0 1 0 9 9 7 7 0 0 1-9-9z"/></svg><span data-i18n="dark_label"><?php echo esc_html( sab_label( 'dark_label' ) ); ?></span></button>
<?php if ( sab_field( 'sab_outro_title' ) || sab_field( 'sab_outro_body' ) ) : ?><section class="outro d-flex align-items-center py-5"><div class="container text-center"><div class="row justify-content-center"><div class="col-12 col-lg-10"><div class="sadu-rule" aria-hidden="true"></div><h2 class="mb-4"><?php echo sab_copy( sab_field( 'sab_outro_title' ) ); ?></h2><p class="mx-auto mb-5"><?php echo esc_html( sab_field( 'sab_outro_body' ) ); ?></p></div></div></div></section><?php endif; ?>
</main><?php get_footer(); ?>
