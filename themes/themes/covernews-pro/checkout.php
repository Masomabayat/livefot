<?php 
/**
 * Template Name: Checkout
 * Description: A blank page template with LTR layout, mobile responsiveness, and static shortcode support for WooCommerce Checkout.
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php wp_title(''); ?></title>
    <?php wp_head(); ?>
    <style>
        body {
            background-color: #F8F8F8;
            color: #181829;
        }

        .checkout-header {
            padding: 15px 0;
            border-bottom: 1px solid #F1F2F4;
            background-color: #FFFFFF;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            text-align: right;
        }

        .cart-link {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            color: #65656B;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .cart-link:hover {
            color: #181829;
        }

        .cart-link:before {
            font-family: WooCommerce;
            content: "\e01d";
            margin-right: 8px;
            font-size: 1.2em;
            color: #23CC8C;
        }

        .cart-count {
            display: inline-block;
            background: #23CC8C;
            color: #FFFFFF;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.875em;
            margin-left: 8px;
        }

        .checkout-content {
            max-width: 1200px;
            margin: 30px auto;
            padding: 30px 20px;
            background-color: #FFFFFF;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        /* WooCommerce form customization */
        .woocommerce form .form-row input.input-text,
        .woocommerce form .form-row textarea {
            background-color: #F3F3F4;
            border: 1px solid #F1F2F4;
            color: #181829;
        }

        .woocommerce form .form-row label {
            color: #65656B;
        }

        .woocommerce #payment {
            background-color: #F3F3F4;
        }

        .woocommerce #payment div.payment_box {
            background-color: #EDF5F2;
        }

        .woocommerce button.button.alt {
            background-color: #23CC8C;
            color: #FFFFFF;
        }

        .woocommerce button.button.alt:hover {
            background-color: #1eb77e;
        }

        /* Error states */
        .woocommerce-error {
            border-color: #CB2027;
            background-color: #fff;
            color: #CB2027;
        }

        @media (max-width: 768px) {
            .header-content,
            .checkout-content {
                padding: 15px;
            }
        }
    </style>
</head>
<body <?php body_class(); ?>>
    <header class="checkout-header">
        <div class="header-content">
            <a class="cart-link" href="<?php echo esc_url(wc_get_cart_url()); ?>">
                Cart
                <?php 
                $cart_count = WC()->cart->get_cart_contents_count();
                if ($cart_count > 0) {
                    echo '<span class="cart-count">' . esc_html($cart_count) . '</span>';
                }
                ?>
            </a>
        </div>
    </header>

    <div class="checkout-content">
        <?php echo do_shortcode('[woocommerce_checkout]'); ?>
    </div>

    <?php wp_footer(); ?>
</body>
</html>