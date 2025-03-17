<?php
/*
Template Name: Full Page LiveFot Matches
*/
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="LiveFot Matches - Full Page">
  <link rel="canonical" href="<?php echo esc_url( home_url( $_SERVER['REQUEST_URI'] ) ); ?>" />
  <title>LiveFot Matches</title>
  <!-- Inline CSS from LiveFootTheme.css -->
  <style>
    <?php echo file_get_contents( get_template_directory() . '/assets/LiveFootTheme.css' ); ?>
  </style>
  <!-- Custom styles for a full-page layout -->
  <style>
    html, body {
      margin: 0;
      padding: 0;
      background-color: #fff;
    }
    .livefot-fullpage-container {
      width: 100%;
      /* Allow the container to grow naturally without forcing full viewport height */
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px; /* optional: add some spacing around the content */
    }
  </style>
  <?php wp_head(); // Loads scripts, styles, and other head elements ?>
</head>
<body <?php body_class(); ?>>
  <div class="livefot-fullpage-container">
    <?php echo do_shortcode('[livefot_matches]'); ?>
  </div>
  <?php wp_footer(); // Loads scripts and other footer elements ?>
</body>
</html>
