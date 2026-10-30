<?php defined( 'ABSPATH' ) || exit; ?>
<footer class="site-footer"><div class="f-inner">
 <div class="f-top"><?php sab_logo( 'footer-logo', true ); ?>
 <div class="f-social"><?php foreach ( (array) sab_option( 'sab_social', true ) as $social ) : if ( ! is_array( $social ) || empty( $social['sab_social_url'] ) ) continue; ?>
  <a href="<?php echo esc_url( $social['sab_social_url'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $social['sab_social_label'] ); ?>"><?php get_template_part( 'template-parts/social-icon', null, array( 'platform' => $social['sab_social_platform'] ) ); ?><span class="screen-reader-text"><?php echo esc_html( $social['sab_social_label'] ); ?></span></a>
 <?php endforeach; ?></div></div>
 <div class="f-rule"></div>
 <?php wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false, 'menu_class' => 'f-legal', 'fallback_cb' => false, 'depth' => 1 ) ); ?>
 <div class="f-meta"><span><?php echo esc_html( sab_option( 'sab_established' ) ); ?></span><span><?php echo esc_html( sab_option( 'sab_copyright' ) ); ?></span></div>
 <p class="f-entity"><?php echo nl2br( esc_html( sab_option( 'sab_legal_text' ) ) ); ?></p>
 <address class="sab-contact" aria-label="<?php echo esc_attr( sab_option( 'sab_contact_label' ) ); ?>">
  <?php if ( $phone = sab_option( 'sab_phone', true ) ) : ?><a dir="ltr" href="tel:<?php echo esc_attr( preg_replace( '/[^+0-9]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a><?php endif; ?>
  <?php if ( $email = sab_option( 'sab_email', true ) ) : ?><a href="mailto:<?php echo esc_attr( sanitize_email( $email ) ); ?>"><?php echo esc_html( $email ); ?></a><?php endif; ?>
  <?php if ( $address = sab_option( 'sab_address' ) ) : ?><?php if ( $map = sab_option( 'sab_map', true ) ) : ?><a href="<?php echo esc_url( $map ); ?>"><?php echo nl2br( esc_html( $address ) ); ?></a><?php else : ?><span><?php echo nl2br( esc_html( $address ) ); ?></span><?php endif; ?><?php endif; ?>
  <?php if ( $site = sab_option( 'sab_website', true ) ) : ?><a href="<?php echo esc_url( $site ); ?>"><?php echo esc_html( sab_option( 'sab_website_label' ) ); ?></a><?php endif; ?>
 </address>
</div></footer>
<?php wp_footer(); ?></body></html>
