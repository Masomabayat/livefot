<?php
namespace Automattic\WooCommerce\Blocks;

use Automattic\WooCommerce\Blocks\AI\Connection;
use Automattic\WooCommerce\Blocks\Images\Pexels;
use Automattic\WooCommerce\Blocks\Domain\Package;
use Automattic\WooCommerce\Blocks\Patterns\PatternsHelper;
use Automattic\WooCommerce\Blocks\Patterns\PatternUpdater;
use Automattic\WooCommerce\Blocks\Patterns\ProductUpdater;

/**
 * Registers patterns under the `./patterns/` directory and updates their content.
 * Each pattern is defined as a PHP file and defines its metadata using plugin-style headers.
 * The minimum required definition is:
 *
 *     /**
 *      * Title: My Pattern
 *      * Slug: my-theme/my-pattern
 *      *
 *
 * The output of the PHP source corresponds to the content of the pattern, e.g.:
 *
 *     <main><p><?php echo "Hello"; ?></p></main>
 *
 * Other settable fields include:
 *
 *   - Description
 *   - Viewport Width
 *   - Categories       (comma-separated values)
 *   - Keywords         (comma-separated values)
 *   - Block Types      (comma-separated values)
 *   - Inserter         (yes/no)
 *
 * @internal
 */
class BlockPatterns {
	const SLUG_REGEX                 = '/^[A-z0-9\/_-]+$/';
	const COMMA_SEPARATED_REGEX      = '/[\s,]+/';
	const PATTERNS_AI_DATA_POST_TYPE = 'patterns_ai_data';

	/**
	 * Path to the patterns directory.
	 *
	 * @var string $patterns_path
	 */
	private $patterns_path;

	/**
	 * Constructor for class
	 *
	 * @param Package $package An instance of Package.
	 */
	public function __construct( Package $package ) {
		$this->patterns_path = $package->get_path( 'patterns' );

		add_action( 'init', array( $this, 'register_block_patterns' ) );
		add_action( 'update_option_woo_ai_describe_store_description', array( $this, 'schedule_on_option_update' ), 10, 2 );
		add_action( 'update_option_woo_ai_describe_store_description', array( $this, 'update_ai_connection_allowed_option' ), 10, 2 );
		add_action( 'upgrader_process_complete', array( $this, 'schedule_on_plugin_update' ), 10, 2 );
		add_action( 'woocommerce_update_patterns_content', array( $this, 'update_patterns_content' ) );
	}

	/**
	 * Make sure the 'woocommerce_blocks_allow_ai_connection' option is set to true if the site is connected to AI.
	 *
	 * @param string $option The option name.
	 * @param string $value The option value.
	 *
	 * @return bool
	 */
	public function update_ai_connection_allowed_option( $option, $value ): bool {
		$ai_connection = new Connection();

		$site_id = $ai_connection->get_site_id();

		if ( is_wp_error( $site_id ) ) {
			return update_option( 'woocommerce_blocks_allow_ai_connection', false );
		}

		$token = $ai_connection->get_jwt_token( $site_id );

		if ( is_wp_error( $token ) ) {
			return update_option( 'woocommerce_blocks_allow_ai_connection', false );
		}

		return update_option( 'woocommerce_blocks_allow_ai_connection', true );
	}

	/**
	 * Registers the block patterns and categories under `./patterns/`.
	 */
	public function register_block_patterns() {
		if ( ! class_exists( 'WP_Block_Patterns_Registry' ) ) {
			return;
		}

		register_post_type(
			self::PATTERNS_AI_DATA_POST_TYPE,
			array(
				'labels'           => array(
					'name'          => __( 'Patterns AI Data', 'woo-gutenberg-products-block' ),
					'singular_name' => __( 'Patterns AI Data', 'woo-gutenberg-products-block' ),
				),
				'public'           => false,
				'hierarchical'     => false,
				'rewrite'          => false,
				'query_var'        => false,
				'delete_with_user' => false,
				'can_export'       => true,
			)
		);

		$default_headers = array(
			'title'         => 'Title',
			'slug'          => 'Slug',
			'description'   => 'Description',
			'viewportWidth' => 'Viewport Width',
			'categories'    => 'Categories',
			'keywords'      => 'Keywords',
			'blockTypes'    => 'Block Types',
			'inserter'      => 'Inserter',
		);

		if ( ! file_exists( $this->patterns_path ) ) {
			return;
		}

		$files = glob( $this->patterns_path . '/*.php' );
		if ( ! $files ) {
			return;
		}

		$dictionary = PatternsHelper::get_patterns_dictionary();

		foreach ( $files as $file ) {
			$pattern_data = get_file_data( $file, $default_headers );

			if ( empty( $pattern_data['slug'] ) ) {
				_doing_it_wrong(
					'register_block_patterns',
					esc_html(
						sprintf(
						/* translators: %s: file name. */
							__( 'Could not register file "%s" as a block pattern ("Slug" field missing)', 'woo-gutenberg-products-block' ),
							$file
						)
					),
					'6.0.0'
				);
				continue;
			}

			if ( ! preg_match( self::SLUG_REGEX, $pattern_data['slug'] ) ) {
				_doing_it_wrong(
					'register_block_patterns',
					esc_html(
						sprintf(
						/* translators: %1s: file name; %2s: slug value found. */
							__( 'Could not register file "%1$s" as a block pattern (invalid slug "%2$s")', 'woo-gutenberg-products-block' ),
							$file,
							$pattern_data['slug']
						)
					),
					'6.0.0'
				);
				continue;
			}

			if ( \WP_Block_Patterns_Registry::get_instance()->is_registered( $pattern_data['slug'] ) ) {
				continue;
			}

			// Title is a required property.
			if ( ! $pattern_data['title'] ) {
				_doing_it_wrong(
					'register_block_patterns',
					esc_html(
						sprintf(
						/* translators: %1s: file name; %2s: slug value found. */
							__( 'Could not register file "%s" as a block pattern ("Title" field missing)', 'woo-gutenberg-products-block' ),
							$file
						)
					),
					'6.0.0'
				);
				continue;
			}

			// For properties of type array, parse data as comma-separated.
			foreach ( array( 'categories', 'keywords', 'blockTypes' ) as $property ) {
				if ( ! empty( $pattern_data[ $property ] ) ) {
					$pattern_data[ $property ] = array_filter(
						preg_split(
							self::COMMA_SEPARATED_REGEX,
							(string) $pattern_data[ $property ]
						)
					);
				} else {
					unset( $pattern_data[ $property ] );
				}
			}

			// Parse properties of type int.
			foreach ( array( 'viewportWidth' ) as $property ) {
				if ( ! empty( $pattern_data[ $property ] ) ) {
					$pattern_data[ $property ] = (int) $pattern_data[ $property ];
				} else {
					unset( $pattern_data[ $property ] );
				}
			}

			// Parse properties of type bool.
			foreach ( array( 'inserter' ) as $property ) {
				if ( ! empty( $pattern_data[ $property ] ) ) {
					$pattern_data[ $property ] = in_array(
						strtolower( $pattern_data[ $property ] ),
						array( 'yes', 'true' ),
						true
					);
				} else {
					unset( $pattern_data[ $property ] );
				}
			}

			// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText, WordPress.WP.I18n.LowLevelTranslationFunction
			$pattern_data['title'] = translate_with_gettext_context( $pattern_data['title'], 'Pattern title', 'woo-gutenberg-products-block' );
			if ( ! empty( $pattern_data['description'] ) ) {
				// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText, WordPress.WP.I18n.LowLevelTranslationFunction
				$pattern_data['description'] = translate_with_gettext_context( $pattern_data['description'], 'Pattern description', 'woo-gutenberg-products-block' );
			}

			$pattern_data_from_dictionary = $this->get_pattern_from_dictionary( $dictionary, $pattern_data['slug'] );

			// The actual pattern content is the output of the file.
			ob_start();

			/*
				For patterns that can have AI-generated content, we need to get its content from the dictionary and pass
				it to the pattern file through the "$content" and "$images" variables.
				This is to avoid having to access the dictionary for each pattern when it's registered or inserted.
				Before the "$content" and "$images" variables were populated in each pattern. Since the pattern
				registration happens in the init hook, the dictionary was being access one for each pattern and
				for each page load. This way we only do it once on registration.
				For more context: https://github.com/woocommerce/woocommerce-blocks/pull/11733
			*/
			if ( ! is_null( $pattern_data_from_dictionary ) ) {
				$content = $pattern_data_from_dictionary['content'];
				$images  = $pattern_data_from_dictionary['images'] ?? array();
			}
			include $file;
			$pattern_data['content'] = ob_get_clean();

			if ( ! $pattern_data['content'] ) {
				continue;
			}

			foreach ( $pattern_data['categories'] as $key => $category ) {
				$category_slug = _wp_to_kebab_case( $category );

				$pattern_data['categories'][ $key ] = $category_slug;

				register_block_pattern_category(
					$category_slug,
					// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
					array( 'label' => __( $category, 'woo-gutenberg-products-block' ) )
				);
			}

			register_block_pattern( $pattern_data['slug'], $pattern_data );
		}
	}

	/**
	 * Update the patterns content when the store description is changed.
	 *
	 * @param string $option The option name.
	 * @param string $value The option value.
	 */
	public function schedule_on_option_update( $option, $value ) {
		$this->schedule_patterns_content_update( $value );
	}

	/**
	 * Update the patterns content when the WooCommerce Blocks plugin is updated.
	 *
	 * @param \WP_Upgrader $upgrader_object  WP_Upgrader instance.
	 * @param array        $options  Array of bulk item update data.
	 */
	public function schedule_on_plugin_update( $upgrader_object, $options ) {
		if ( 'update' === $options['action'] && 'plugin' === $options['type'] && isset( $options['plugins'] ) ) {
			foreach ( $options['plugins'] as $plugin ) {
				if ( str_contains( $plugin, 'woocommerce-gutenberg-products-block.php' ) || str_contains( $plugin, 'woocommerce.php' ) ) {
					$business_description = get_option( 'woo_ai_describe_store_description' );

					if ( $business_description ) {
						$this->schedule_patterns_content_update( $business_description );
					}
				}
			}
		}
	}

	/**
	 * Update the patterns content when the store description is changed.
	 *
	 * @param string $business_description The business description.
	 */
	public function schedule_patterns_content_update( $business_description ) {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		$action_scheduler = WP_PLUGIN_DIR . '/woocommerce/packages/action-scheduler/action-scheduler.php';

		if ( ! file_exists( $action_scheduler ) ) {
			return;
		}

		require_once $action_scheduler;

		as_schedule_single_action( time(), 'woocommerce_update_patterns_content', array( $business_description ) );
	}

	/**
	 * Update the patterns content.
	 *
	 * @param string $value The new value saved for the add_option_woo_ai_describe_store_description option.
	 *
	 * @return bool|string|\WP_Error
	 */
	public function update_patterns_content( $value ) {
		$allow_ai_connection = get_option( 'woocommerce_blocks_allow_ai_connection' );

		if ( ! $allow_ai_connection ) {
			return new \WP_Error(
				'ai_connection_not_allowed',
				__( 'AI content generation is not allowed on this store. Update your store settings if you wish to enable this feature.', 'woo-gutenberg-products-block' )
			);
		}

		$ai_connection = new Connection();

		$site_id = $ai_connection->get_site_id();

		if ( is_wp_error( $site_id ) ) {
			return $site_id->get_error_message();
		}

		$token = $ai_connection->get_jwt_token( $site_id );

		if ( is_wp_error( $token ) ) {
			return $token->get_error_message();
		}

		$business_description = get_option( 'woo_ai_describe_store_description' );

		$images = ( new Pexels() )->get_images( $ai_connection, $token, $business_description );

		if ( is_wp_error( $images ) ) {
			return $images->get_error_message();
		}

		$populate_patterns = ( new PatternUpdater() )->generate_content( $ai_connection, $token, $images, $business_description );

		if ( is_wp_error( $populate_patterns ) ) {
			return $populate_patterns->get_error_message();
		}

		$populate_products = ( new ProductUpdater() )->generate_content( $ai_connection, $token, $images, $business_description );

		if ( is_wp_error( $populate_products ) ) {
			return $populate_products->get_error_message();
		}

		return true;
	}

	/**
	 * Filter the patterns dictionary to get the pattern data corresponding to the pattern slug.
	 *
	 * @param array  $dictionary The patterns dictionary.
	 * @param string $slug The pattern slug.
	 *
	 * @return array|null
	 */
	private function get_pattern_from_dictionary( $dictionary, $slug ) {
		foreach ( $dictionary as $pattern_dictionary ) {
			if ( $pattern_dictionary['slug'] === $slug ) {
				return $pattern_dictionary;
			}
		}

		return null;
	}
}
