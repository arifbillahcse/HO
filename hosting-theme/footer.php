<?php
/**
 * The footer template for the Hostorio theme.
 *
 * Contains the main footer, footer bottom bar, and closing tags.
 *
 * @package Hosting_Theme
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- ===========================
     MAIN FOOTER
     =========================== -->
<footer class="main-footer">
    <div class="container-wrapper">
        <div class="footer-content">

            <!-- Column 1: About -->
            <div class="footer-column footer-about">
                <div class="footer-logo">
                    <h3><span class="logo-host">HOST</span><span class="logo-orio">ORIO</span></h3>
                </div>
                <p class="footer-description">
                    <?php esc_html_e( 'Hostorio is a popular web hosting services provider. Hostorio provides Shared Hosting, Business Hosting, Reseller Hosting, VPS, and domain Registration-Transfer services.', 'hosting-theme' ); ?>
                </p>
                <div class="social-links">
                    <a href="#" class="social-icon" aria-label="<?php esc_attr_e( 'Facebook', 'hosting-theme' ); ?>">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="social-icon" aria-label="<?php esc_attr_e( 'Twitter', 'hosting-theme' ); ?>">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="social-icon" aria-label="<?php esc_attr_e( 'YouTube', 'hosting-theme' ); ?>">
                        <i class="fab fa-youtube"></i>
                    </a>
                    <a href="#" class="social-icon" aria-label="<?php esc_attr_e( 'LinkedIn', 'hosting-theme' ); ?>">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                </div>
            </div>

            <!-- Column 2: Important -->
            <div class="footer-column">
                <h4 class="footer-title"><?php esc_html_e( 'IMPORTANT', 'hosting-theme' ); ?></h4>
                <ul class="footer-links">
                    <li>
                        <a href="#">
                            <i class="fas fa-caret-right"></i>
                            <span><?php esc_html_e( 'Log In', 'hosting-theme' ); ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-caret-right"></i>
                            <span><?php esc_html_e( 'Support', 'hosting-theme' ); ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-caret-right"></i>
                            <span><?php esc_html_e( 'Contact Us', 'hosting-theme' ); ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-caret-right"></i>
                            <span><?php esc_html_e( 'Privacy Policy', 'hosting-theme' ); ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-caret-right"></i>
                            <span><?php esc_html_e( 'Terms & Conditions', 'hosting-theme' ); ?></span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Column 3: Quick Links -->
            <div class="footer-column">
                <h4 class="footer-title"><?php esc_html_e( 'QUICK LINKS', 'hosting-theme' ); ?></h4>
                <ul class="footer-links">
                    <li>
                        <a href="#">
                            <i class="fas fa-caret-right"></i>
                            <span><?php esc_html_e( 'Shared Hosting', 'hosting-theme' ); ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-caret-right"></i>
                            <span><?php esc_html_e( 'Business Hosting', 'hosting-theme' ); ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-caret-right"></i>
                            <span><?php esc_html_e( 'Business Email', 'hosting-theme' ); ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-caret-right"></i>
                            <span><?php esc_html_e( 'Reseller Hosting', 'hosting-theme' ); ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-caret-right"></i>
                            <span><?php esc_html_e( 'VPS', 'hosting-theme' ); ?></span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Column 4: Contact Us -->
            <div class="footer-column">
                <h4 class="footer-title"><?php esc_html_e( 'CONTACT US', 'hosting-theme' ); ?></h4>
                <ul class="footer-contact">
                    <li>
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Kanchan, Rupganj, Narayanganj</span>
                    </li>
                    <li>
                        <i class="fas fa-map-marker-alt"></i>
                        <span>H-161, OABDA SOROK MATHBARIA</span>
                    </li>
                    <li>
                        <i class="fas fa-phone-alt"></i>
                        <span>+8801779440297</span>
                    </li>
                    <li>
                        <i class="fas fa-envelope"></i>
                        <span>mail@hostorio.com</span>
                    </li>
                </ul>
                <div class="payment-methods">
                    <img src="<?php echo esc_url( HOSTING_THEME_URI . '/assets/images/payment/payment-gateway.svg' ); ?>" alt="<?php esc_attr_e( 'Payment Methods', 'hosting-theme' ); ?>">
                </div>
            </div>

        </div>
    </div>
</footer>

<!-- ===========================
     FOOTER BOTTOM
     =========================== -->
<div class="footer-bottom">
    <div class="container-wrapper">
        <div class="footer-bottom-content">
            <p class="copyright">
                &copy; <?php echo esc_html( date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All Rights Reserved.', 'hosting-theme' ); ?>
            </p>
            <div class="footer-bottom-links">
                <a href="#"><?php esc_html_e( 'Privacy Policy', 'hosting-theme' ); ?></a>
                <span class="separator">|</span>
                <a href="#"><?php esc_html_e( 'Terms of Service', 'hosting-theme' ); ?></a>
            </div>
        </div>
    </div>
</div>

<!-- WhatsApp Floating Button -->
<a href="https://wa.me/8801779440297" class="whatsapp-float" target="_blank" rel="noopener noreferrer" aria-label="Chat on WhatsApp">
    <i class="fab fa-whatsapp"></i>
    <span class="whatsapp-tooltip">Chat with us</span>
</a>

<style>
.whatsapp-float {
    position: fixed;
    left: 20px;
    bottom: 30px;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 56px;
    height: 56px;
    background: #25D366;
    border-radius: 50%;
    box-shadow: 0 4px 16px rgba(37, 211, 102, 0.45);
    color: #fff;
    font-size: 30px;
    text-decoration: none;
    transition: transform 0.2s, box-shadow 0.2s;
}
.whatsapp-float:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 24px rgba(37, 211, 102, 0.6);
    color: #fff;
}
.whatsapp-tooltip {
    position: absolute;
    left: 66px;
    background: #25D366;
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    padding: 5px 12px;
    border-radius: 20px;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}
.whatsapp-float:hover .whatsapp-tooltip {
    opacity: 1;
}
</style>

<?php wp_footer(); ?>
</body>
</html>
