<?php
/**
 * Add the about page under appearance.
 *
 * Display the details about the theme information
 *
 * @package freenews
 */
?>
<?php
// About Information
add_action( 'admin_menu', 'freenews_about' );
function freenews_about() {    	
	add_theme_page( esc_html__('About Theme', 'freenews'), esc_html__('About Theme', 'freenews'), 'edit_theme_options', 'freenews-about', 'freenews_about_page');   
}

// CSS for About Theme Page
function freenews_admin_theme_style() {
   wp_enqueue_style('freenews-admin-style', get_template_directory_uri() . '/inc/about-theme/css/about-theme.css');
}
add_action('admin_enqueue_scripts', 'freenews_admin_theme_style');

function freenews_about_page() {
	$theme = wp_get_theme();

?>
<div class="wrapper-info">
	<div class="col-left">
		<div class="intro">
			<h3><?php /* translators: %s theme name */
				printf( esc_html__( 'Welcome to %s', 'freenews' ), esc_html( $theme->Name ) ); ?>
				<?php esc_html_e('Version:','freenews'); ?> <?php echo esc_html($theme['Version']);?></h3>
				<p>
					<?php esc_html_e('FreeNews Magazine theme is an amazing modern WordPress theme. You can choose to build professional websites. It is easy to customize for all kinds of news, newspaper, magazine, publishing, blog or review sites. It also include all major aspects like responsive, performance, cross-browser compatible, SEO ready and supports RTL. It is ready to promotion with social media icons to reach maximum target audience. Responsive slider impress your customers with lively eye-catching images right on your banner section.','freenews'); ?>
				</p>
				<p>
				<?php /* translators: %s theme name */
					printf( esc_html__( '%s theme is designed with passion. Please click the below button to display how your site looks like', 'freenews' ), esc_html( $theme->Name ) );
				?></p>
				<p> &nbsp;</p>
				<a href="<?php echo esc_url('https://demo.themespiral.com/freenews'); ?>" class="button button-primary button-hero about-theme" target="_blank"><?php esc_html_e( 'Visit Free Demo', 'freenews' ); ?></a><a href="<?php echo esc_url('https://demo.themespiral.com/freenews-pro'); ?>" class="button button-primary button-hero about-theme" target="_blank"><?php esc_html_e( 'Visit Pro Demo', 'freenews' ); ?></a>
		</div>
		<div class="theme-tabs">
			<input type="radio" name="nav" id="one" checked="checked"/>
			<label for="one" class="tab-label"><?php esc_html_e('Getting Started?','freenews');?></label>

			<input type="radio" name="nav" id="two"/>
			<label for="two" class="tab-label"><?php esc_html_e('Demo Importer','freenews');?></label>

			<input type="radio" name="nav" id="three"/>
			<label for="three" class="tab-label"><?php esc_html_e('Support','freenews');?></label>

			<input type="radio" name="nav" id="four"/>
			<label for="four" class="tab-label"><?php esc_html_e('Video Tutorials','freenews');?></label>

			<input type="radio" name="nav" id="five"/>
			<label for="five" class="tab-label"><?php esc_html_e('Pro Features','freenews');?></label>

			<article class="content one">
			    <h3><?php esc_html_e('About Documentation','freenews');?></h3>
			    <p><?php esc_html_e('Documentation is the information that describes the product to its users. Our documentation covers only related to Free Themes and Pro Extension Plugins. It will guide your to develop your Website as we displayed in demo site without any others help.','freenews');?></p>
			    <p>
					<a href="<?php echo esc_url('https://docs.themespiral.com/freenews/');?>" target="_blank" class="button button-primary"><?php printf( esc_html__( '%s Documentation', 'freenews' ), esc_html( $theme->Name ) ); ?></a>
				</p>
				<h3><?php esc_html_e('Theme Customizer','freenews');?></h3>
			   <p><?php printf( esc_html__( '%s supports the Theme Customizer for all theme settings. Click "Customize" to personalize your site.', 'freenews' ), esc_html( $theme->Name ) ); ?>
			   	<a href="<?php echo esc_url(admin_url( 'customize.php' )); ?>" target="_blank" class="button button-primary"> <?php esc_html_e('Start Customizing','freenews');?></a>
				</p>
				<h3><?php esc_html_e('F.A.Q (Frequently Asked Questions)','freenews');?></h3>
			   <p><?php esc_html_e('Want to know more about Themes and Plugins developed by Theme Spiral? ','freenews'); ?><a href="<?php echo esc_url('https://themespiral.com/f-a-q/');?>" class="button button-primary" target="_blank"><?php esc_html_e('F.A.Q','freenews');?></a></p>
				<img src="<?php echo esc_url( get_template_directory_uri() ); ?>/screenshot.jpg">
			</article>

			<article class="content two">
			    <h3><?php esc_html_e('Demo Importer','freenews');?></h3>
				<p>
					<?php esc_html_e( 'If your site have your own content then do not use this plugins to import dummy content. It will mess your site with dummy content. Is your site fresh? Install the Demo importer plugins and activate it.', 'freenews' ); ?></p>
				<p><?php esc_html_e('Do you want to install One Click Demo Import Plugin? ','freenews'); ?></p>
					<?php if ( is_plugin_active( 'one-click-demo-import/one-click-demo-import.php' ) ) { ?>
					<a href="<?php echo esc_url( admin_url( 'themes.php?page=pt-one-click-demo-import' ) ) ?>" class="button button-primary" style="text-decoration: none;">
						<?php esc_html_e( 'Install Demo Plugin', 'freenews' ); ?>
					</a>
				<?php } else { ?>
					<a href="<?php echo esc_url( admin_url( 'themes.php?page=tgmpa-install-plugins' ) ) ?>" class="button button-primary" style="text-decoration: none;">
						<?php esc_html_e( 'Install Demo Plugin', 'freenews' ); ?>
					</a>
				<?php } ?> &nbsp;&nbsp;

				<h3><?php esc_html_e('How to install Dummy Content ?','freenews');?></h3>

				<p><?php esc_html_e(' Please install One Click Demo Import plugins. You can install it after activating freenews theme. It is listed in recommended Plugins','freenews'); ?></p>
				<ul>
					<li><?php esc_html_e('After plugin is activated, it asks you to upload  XML, WIE and  DAT dummy file','freenews');?></li>
					<li><a href="https://themespiral.com/download/1248/" target="_blank"><?php esc_html_e('Download it from Here ','freenews');?></a></li>
					<li><?php esc_html_e('Unzip freenews-dummy-content.zip file. You can find all XML, WIE and  DAT dummy file','freenews');?></li>
					<li><?php esc_html_e('Navigate to Appearance > Import Demo Data','freenews');?> 
					<?php if ( is_plugin_active( 'one-click-demo-import/one-click-demo-import.php' ) ) { ?> <a href="<?php echo esc_url( admin_url( 'themes.php?page=pt-one-click-demo-import' ) ) ?>"><?php esc_html_e('Upload','freenews'); ?></a><?php } ?></li>
					<li><?php esc_html_e('Upload manually and Click on Import demo data.','freenews');?></li>
					<li><?php esc_html_e('Now all your files and settings has been imported. Now you just need to setup your menu and front page.','freenews');?></li>
				</ul>
				<p><?php esc_html_e('Now all your files and settings has been imported. Now you just need to setup your menu and front page.','freenews');?></p>
				<p><strong><?php esc_html_e('Setup Menu:','freenews');?> </strong></p>
				
				<ul>
					<li><?php esc_html_e('In the Blog Dashboard, select Appearance > Menus.','freenews');?></li>
					<li><?php esc_html_e('Under the Menu Settings, located at the bottom of your screen, select Primary/ Secondary menu','freenews');?></li>
					<li><?php esc_html_e('Click save menu','freenews');?></li>
				</ul>
				<p><strong><?php esc_html_e('Setup Home Page:','freenews');?></strong></p>
				<ul>
					<li><?php esc_html_e('Navigate to Dashboard > Reading > Click on ( A static page ) from Your homepage displays','freenews');?></li>
				
				<li><?php esc_html_e('Select Homepage as Home and Postpage as Blog','freenews');?></li>
			</ul>
				<a href="https://www.youtube.com/watch?v=PmoUGhFnMiw" class="button button-primary" style="text-decoration: none;" target="_blank">
						<?php esc_html_e( 'Watch Demo Import Video', 'freenews' ); ?></a>
				<img src="<?php echo esc_url( get_template_directory_uri() ); ?>/screenshot.jpg">
			</article>

			<article class="content three">
			   <h3><?php esc_html_e('About Support','freenews');?></h3>
				<p><?php esc_html_e('Need Help? Use our Forums if you have any Themes and Plugins related questions. Support will be provided only related to our Themes and Plugins','freenews');?>
					<a href="<?php echo esc_url('https://themespiral.com/forums/'); ?>" target="_blank" class="button button-primary"> <?php esc_html_e('Forums','freenews');?></a>
				</p>
				<h3><?php esc_html_e('Sales Questions','freenews');?></h3>
				<p><?php esc_html_e('Do you have discussion relating to billing, your account or have pre-sales questions? Get touch with us!','freenews');?>
					<a href="<?php echo esc_url('https://themespiral.com/contact-us/');?>" target="_blank" class="button button-primary"> <?php esc_html_e('Contact us','freenews');?></a>
				</p>
			   <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/screenshot.jpg">
			</article>

			<article class="content four">
			   <h3><?php esc_html_e('Video Tutorials','freenews');?></h3>
				<h4> <?php esc_html_e('Setup Site Identity','freenews'); ?></h4>
					<a class="button button-secondary" href="<?php echo esc_url(admin_url('customize.php?autofocus[section]=title_tagline')); ?>"></span><?php esc_html_e( 'Site Identity', 'freenews' ); ?></a>

				<h4> <?php esc_html_e('Setup Main Banner','freenews'); ?></h4>
				<a class="button button-primary" target="_blank" href="https://www.youtube.com/watch?v=W_uar3xGjTQ"><?php esc_html_e( 'Watch Video', 'freenews' ); ?></a>
					<a class="button button-secondary" href="<?php echo esc_url(admin_url('customize.php?autofocus[section]=freenews_main_banner_section')); ?>"></span><?php esc_html_e( 'Main Banner', 'freenews' ); ?></a>

				<h4> <?php esc_html_e('Setup Highlighted Category','freenews'); ?></h4>
					<a class="button button-secondary" href="<?php echo esc_url(admin_url('customize.php?autofocus[section]=freenews_highlighted_category_section')); ?>"></span><?php esc_html_e( 'Highlighted Category', 'freenews' ); ?></a>

				<h4> <?php esc_html_e('Setup Social Icons','freenews'); ?></h4>
					<a class="button button-secondary" href="<?php echo esc_url(admin_url())?>nav-menus.php"></span><?php esc_html_e( 'Social Icons', 'freenews' ); ?></a>

				<h4> <?php esc_html_e('Setup Home Template','freenews'); ?></h4>
				<a class="button button-primary" target="_blank" href="https://www.youtube.com/watch?v=m14EBjO3Il8"><?php esc_html_e( 'Watch Video', 'freenews' ); ?></a>

				<h4> <?php esc_html_e('Setup Primary Menu','freenews'); ?></h4>
					<a class="button button-secondary" href="<?php echo esc_url(admin_url())?>nav-menus.php"></span><?php esc_html_e( 'Primary Menu', 'freenews' ); ?></a>

				<h4> <?php esc_html_e('Setup Header','freenews'); ?></h4>
					<a class="button button-secondary" href="<?php echo esc_url(admin_url('customize.php?autofocus[section]=header_image')); ?>"></span><?php esc_html_e( 'Setup Header', 'freenews' ); ?></a>
			</article>

			<article class="content five">
				 <h3><?php esc_html_e('Upgrade to Pro','freenews');?></h3>
				 <p><?php esc_html_e('Want additional features? Pro extension plugin adds additinal features for free themes. ','freenews')?><a href="<?php echo esc_url('https://themespiral.com/themes/freenews');?>" class="button button-primary button-hero" target="_blank"><?php esc_html_e('Upgrade to Pro','freenews');?></a></p>
			   <h3><?php esc_html_e('Pro Features Extension','freenews');?></h3>
				<div class="feature-content">
					<ul class="feature-text">
						<li><?php esc_html_e('Site Layout','freenews'); ?></li>
						<li><?php esc_html_e('Single Sidebar Layout','freenews'); ?></li>
						<li><?php esc_html_e('Flexible Content Width','freenews'); ?></li>
						<li><?php esc_html_e('Sidebar Content Width','freenews'); ?></li>
						<li><?php esc_html_e('Custom Design','freenews'); ?></li>
						<li><?php esc_html_e('Default Text Edit','freenews'); ?></li>
						<li><?php esc_html_e('Choose Main Banner','freenews'); ?></li>
						<li><?php esc_html_e('Category highlight settings','freenews'); ?></li>
						<li><?php esc_html_e('Excerpt Text edit','freenews'); ?></li>
						<li><?php esc_html_e('Footer Layout','freenews'); ?></li>
						<li><?php esc_html_e('Instagram Compatible','freenews'); ?></li>
						<li><?php esc_html_e('Unlimited Color','freenews'); ?></li>
						<li><?php esc_html_e('Font Color','freenews'); ?></li>
						<li><?php esc_html_e('Color Schemes','freenews'); ?></li>
						<li><?php esc_html_e('Background Color','freenews'); ?></li>
						<li><?php esc_html_e('Font Size','freenews'); ?></li>
						<li><?php esc_html_e('Font Family','freenews'); ?></li>
						<li><?php esc_html_e('Footer Column 1/2/3/4','freenews'); ?></li>
						<li><?php esc_html_e('More Social Icons','freenews'); ?></li>
						<li><?php esc_html_e('Change Featured Text in Sticky Post','freenews'); ?></li>
						<li><?php esc_html_e('Standard Section','freenews'); ?></li>
						<li><?php esc_html_e('Standard Section Column 3/4/5','freenews'); ?></li>
					</ul>
			    </div><!-- .feature-content -->
			</article>
		</div>
		<div class="pro-content">
			<div class="pro-content-wrap">
				<div class="pro-content-header">
					<h3><?php esc_html_e('Powerful Pro Extension Features','freenews');?></h3>
					<p><?php esc_html_e('Get unlimited features using Pro extension. Purchase FreeNews Pro extension and get additional features and advanced customization options to make your website look awesome in different styles. ','freenews'); ?></p>
				</div>
					<img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/free_vs_pro.png" alt="<?php esc_attr_e('Free vs Pro','freenews');?>">
			</div>
		</div>
	</div>
</div>
<?php }