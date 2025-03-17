<?php
/**
 * Template Name: Blank Shop
 * Description: A blank page template with LTR layout, mobile responsiveness, and no theme header/footer—dedicated to WooCommerce Shop.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php wp_title(''); ?></title>
    <?php wp_head(); ?>
    <style>
        /* Basic reset and responsive container styling */
        body {
            margin: 0;
            padding: 0;
            direction: ltr; /* or remove if your site is RTL */
            font-family: Arial, sans-serif;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
    </style>
</head>
<body <?php body_class(); ?>>
    <div class="container">
        <?php
            /**
             * This outputs the main WooCommerce Shop or product archive
             * IF this page is set as the "Shop" page under
             * WooCommerce > Settings > Products > General.
             *
             * Alternatively, if you visit a product category or single product
             * from this template, WooCommerce will still show relevant content.
             */
            woocommerce_content();
        ?>
    </div>

    <?php wp_footer(); ?>
</body>
</html>
