<?php 
/**
 * Template Name: Blank Responsive Dynamic Shortcode Page Template
 * Description: A blank page template with LTR layout, mobile responsiveness, and static shortcode support.
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php wp_title(''); ?></title>
    <?php wp_head(); ?>
    <style>
        /* Basic reset and responsive container styling */
        body {
            margin: 0;
            padding: 0;
            direction: ltr;
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
            // Execute the static shortcode.
            echo do_shortcode('[woocommerce_cart]');
        ?>
    </div>
    <?php wp_footer(); ?>
</body>
</html>
