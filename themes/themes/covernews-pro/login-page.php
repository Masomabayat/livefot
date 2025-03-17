<?php
/**
 * Template Name: Blank Login Page
 *
 * A minimal template that only displays the FotLive Login shortcode.
 */
?>
<!DOCTYPE html>
<html lang="en-US" dir="ltr">

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <div id="login-page-container">
        <?php echo do_shortcode('[fotlive_login]'); ?>
    </div>
    <?php wp_footer(); ?>
</body>
</html>
