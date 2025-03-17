<?php
/**
 * Plugin Name: Unique Codes for WooCommerce Products
 * Description: Assigns unique codes to WooCommerce orders for specific products (1 League, 25 Leagues, 40 Leagues, and 55 Leagues) and sends the code via email.
 * Version: 1.3
 * Author: Your Name
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Unique_Codes_WooCommerce {

	/**
	 * Constructor: hooks into activation, order processing & completion, admin actions, and email customization.
	 */
	public function __construct() {
		// Create the custom table on plugin activation.
		register_activation_hook( __FILE__, array( $this, 'activate_plugin' ) );
		
		// When an order is set to processing, assign a unique code.
		add_action( 'woocommerce_order_status_processing', array( $this, 'assign_unique_codes' ) );
		
		// When an order is completed, assign a unique code.
		add_action( 'woocommerce_order_status_completed', array( $this, 'assign_unique_codes' ) );
		
		// Add our admin menu page.
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		
		// Process our admin form submission.
		add_action( 'admin_post_add_unique_codes', array( $this, 'handle_form_submission' ) );
		
		// Add the unique code to order confirmation emails.
		add_action( 'woocommerce_email_after_order_table', array( $this, 'add_unique_code_to_email' ), 10, 3 );
	}

	/**
	 * Plugin activation: create the custom table to store unique codes.
	 */
	public function activate_plugin() {
		global $wpdb;
		$table_name     = $wpdb->prefix . 'unique_codes';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			product_type varchar(50) NOT NULL,
			code varchar(255) NOT NULL,
			used tinyint(1) NOT NULL DEFAULT 0,
			order_id bigint(20) DEFAULT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	/**
	 * Assign an unused unique code to each eligible order item when the order is processed or completed.
	 * This version uses a database transaction to lock the row and prevent duplicate assignments.
	 *
	 * @param int $order_id The order ID.
	 */
	public function assign_unique_codes( $order_id ) {
		global $wpdb;
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		// Define which product names should get a unique code.
		$allowed_products = array( '1 League', '25 Leagues', '40 Leagues', '55 Leagues' );

		// Loop through each item in the order.
		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();
			if ( ! $product ) {
				continue;
			}
			$product_name = $product->get_name();

			// Check if this product is one of the allowed types.
			if ( in_array( $product_name, $allowed_products, true ) ) {

				// Check if a code has already been assigned to avoid duplicates.
				$meta_key = '_unique_code_' . sanitize_title( $product_name );
				if ( get_post_meta( $order_id, $meta_key, true ) ) {
					continue;
				}

				$table_name = $wpdb->prefix . 'unique_codes';

				// Start the transaction.
				$wpdb->query( 'START TRANSACTION' );

				// Select an unused code and lock it for update.
				$code = $wpdb->get_var( $wpdb->prepare(
					"SELECT code FROM $table_name WHERE product_type = %s AND used = 0 LIMIT 1 FOR UPDATE",
					$product_name
				) );

				if ( $code ) {
					// Attempt to update the code as used and assign the order ID.
					$update_result = $wpdb->update(
						$table_name,
						array(
							'used'     => 1,
							'order_id' => $order_id,
						),
						array(
							'code' => $code,
							'used' => 0,
						)
					);

					// If update is successful, commit the transaction and save the order meta.
					if ( $update_result !== false ) {
						update_post_meta( $order_id, $meta_key, $code );
						$wpdb->query( 'COMMIT' );
					} else {
						// If update failed, rollback the transaction.
						$wpdb->query( 'ROLLBACK' );
					}
				} else {
					// No available code; commit the transaction.
					$wpdb->query( 'COMMIT' );
				}
			}
		}
	}

	/**
	 * Add an admin submenu page under Tools.
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'tools.php',
			'Unique Codes Manager',
			'Unique Codes',
			'manage_options',
			'unique-codes-manager',
			array( $this, 'admin_page' )
		);
	}

	/**
	 * Output the admin page for managing unique codes.
	 */
	public function admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1>Unique Codes Manager</h1>
			<p>Use the form below to add unique codes for each product type. Enter one code per line.</p>
			<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
				<input type="hidden" name="action" value="add_unique_codes">
				<?php wp_nonce_field( 'add_unique_codes_nonce', 'add_unique_codes_nonce_field' ); ?>
				<table class="form-table">
					<tr>
						<th scope="row"><label for="product_type">Product Type</label></th>
						<td>
							<select name="product_type" id="product_type">
								<option value="1 League">1 League</option>
								<option value="25 Leagues">25 Leagues</option>
								<option value="40 Leagues">40 Leagues</option>
								<option value="55 Leagues">55 Leagues</option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="codes">Unique Codes (one per line)</label></th>
						<td>
							<textarea name="codes" id="codes" rows="10" cols="50"></textarea>
						</td>
					</tr>
				</table>
				<?php submit_button( 'Add Codes' ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Handle the admin form submission to add unique codes.
	 */
	public function handle_form_submission() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized user' );
		}
		if ( ! isset( $_POST['add_unique_codes_nonce_field'] ) || ! wp_verify_nonce( $_POST['add_unique_codes_nonce_field'], 'add_unique_codes_nonce' ) ) {
			wp_die( 'Nonce verification failed' );
		}

		global $wpdb;
		$table_name   = $wpdb->prefix . 'unique_codes';
		$product_type = sanitize_text_field( $_POST['product_type'] );
		$codes_input  = sanitize_textarea_field( $_POST['codes'] );
		$codes        = array_filter( array_map( 'trim', explode( "\n", $codes_input ) ) );

		if ( ! empty( $codes ) ) {
			foreach ( $codes as $code ) {
				$wpdb->insert(
					$table_name,
					array(
						'product_type' => $product_type,
						'code'         => $code,
						'used'         => 0,
					)
				);
			}
		}

		wp_redirect( admin_url( 'tools.php?page=unique-codes-manager' ) );
		exit;
	}

	/**
	 * Add the unique code to the order confirmation email.
	 *
	 * @param WC_Order $order Order object.
	 * @param bool     $sent_to_admin Indicates if email is sent to admin.
	 * @param bool     $plain_text Indicates if email is plain text.
	 */
	public function add_unique_code_to_email( $order, $sent_to_admin, $plain_text ) {
		// Define the allowed product names.
		$allowed_products = array( '1 League', '25 Leagues', '40 Leagues', '55 Leagues' );

		foreach ( $allowed_products as $product ) {
			// Retrieve the code saved in the order meta.
			$meta_key = '_unique_code_' . sanitize_title( $product );
			$code     = get_post_meta( $order->get_id(), $meta_key, true );

			if ( $code ) {
				if ( $plain_text ) {
					echo "\nUnique code for " . $product . ": " . $code . "\n";
				} else {
					echo '<p><strong>Unique code for ' . esc_html( $product ) . ':</strong> ' . esc_html( $code ) . '</p>';
				}
			}
		}
	}
}

// Initialize the plugin.
new Unique_Codes_WooCommerce();
