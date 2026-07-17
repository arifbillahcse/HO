<?php
/**
 * WooCommerce template wrapper.
 *
 * Wraps all WooCommerce pages (My Account, Cart, Checkout, Shop)
 * in the theme's standard header and footer.
 *
 * @package Hosting_Theme
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main class="site-main woocommerce-page" style="padding: 60px 0;">
    <div class="container-wrapper">
        <?php woocommerce_content(); ?>
    </div>
</main>

<?php
get_footer();
