<?php defined( 'ABSPATH' ) || exit; $leaders = sab_records( 'sab_leader' ); ?>
<section class="leadership-section" id="leadershipSection" aria-labelledby="leadership-heading">
 <div class="leadership-header"><h2 class="eyebrow" id="leadership-heading"><?php echo esc_html( sab_label( 'leadership_eyebrow' ) ); ?></h2></div>
 <?php foreach ( $leaders as $i => $leader ) : ?>
 <article class="leader-block<?php echo $i % 2 ? ' reverse' : ''; ?>">
  <div class="leader-side"><?php echo get_the_post_thumbnail( $leader, 'large', array( 'class' => 'leader-portrait', 'alt' => get_the_title( $leader ) ) ); ?></div>
  <div class="leader-text"><div class="leader-quote">
   <h3 class="leader-role"><?php echo esc_html( get_the_title( $leader ) ); ?></h3>
   <?php if ( $role = sab_field( 'sab_role', $leader->ID ) ) : ?><p><?php echo esc_html( $role ); ?></p><?php endif; ?>
   <?php if ( $quote = sab_field( 'sab_pull_quote', $leader->ID ) ) : ?><blockquote class="leader-pull"><?php echo esc_html( $quote ); ?></blockquote><?php endif; ?>
   <?php echo apply_filters( 'the_content', $leader->post_content ); ?>
  </div></div>
 </article>
 <?php endforeach; ?>
</section>
