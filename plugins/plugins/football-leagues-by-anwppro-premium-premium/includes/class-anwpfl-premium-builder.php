<?php

/**
 * AnWP Football Leagues Premium :: Builder
 *
 * @since 0.8.15
 */
class AnWPFL_Premium_Builder {

	/**
	 * Parent plugin class.
	 *
	 * @var AnWP_Football_Leagues_Premium
	 * @since  0.8.15
	 */
	protected $plugin = null;

	/**
	 * Current post Builder Layout Id
	 *
	 * @var bool|int
	 * @since 0.14.7
	 */
	private static $layout_id = false;

	/**
	 * Current post Builder Layout Type
	 *
	 * @var string
	 * @since 0.14.7
	 */
	private static $layout_type = '';

	/**
	 * SEO Page Title
	 *
	 * @var string
	 * @since 0.14.7
	 */
	private static $layout_seo_title = '';

	/**
	 * SEO Description
	 *
	 * @var string
	 * @since 0.14.7
	 */
	private static $layout_seo_description = '';

	/**
	 * Builder Types.
	 *
	 * @var array
	 */
	public $types = [];

	/**
	 * Constructor.
	 * Register Custom Post Types.
	 *
	 * @param AnWP_Football_Leagues_Premium $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {

		// Save core plugin to var
		$this->plugin = $plugin;

		// Register CPT
		$this->register_post_type();

		// Init
		$this->init();

		// Run hooks
		$this->hooks();
	}

	/**
	 * Init Builder Types.
	 */
	public function init() {

		AnWP_Football_Leagues_Premium::include_file( 'includes/builder/class-anwpfl-premium-builder-interface' );

		$this->types['club']              = AnWP_Football_Leagues_Premium::include_file( 'includes/builder/class-anwpfl-premium-builder-club' );
		$this->types['competition']       = AnWP_Football_Leagues_Premium::include_file( 'includes/builder/class-anwpfl-premium-builder-competition' );
		$this->types['competition_multi'] = AnWP_Football_Leagues_Premium::include_file( 'includes/builder/class-anwpfl-premium-builder-competition-multi' );
		$this->types['match']             = AnWP_Football_Leagues_Premium::include_file( 'includes/builder/class-anwpfl-premium-builder-match' );
		$this->types['match_live']        = AnWP_Football_Leagues_Premium::include_file( 'includes/builder/class-anwpfl-premium-builder-match-live' );
		$this->types['player']            = AnWP_Football_Leagues_Premium::include_file( 'includes/builder/class-anwpfl-premium-builder-player' );
		$this->types['referee']           = AnWP_Football_Leagues_Premium::include_file( 'includes/builder/class-anwpfl-premium-builder-referee' );
		$this->types['stadium']           = AnWP_Football_Leagues_Premium::include_file( 'includes/builder/class-anwpfl-premium-builder-stadium' );
		$this->types['staff']             = AnWP_Football_Leagues_Premium::include_file( 'includes/builder/class-anwpfl-premium-builder-staff' );
	}

	/**
	 * Register Custom Post Type
	 */
	public function register_post_type() {

		// Register this CPT.
		$labels = [
			'name'               => _x( 'Layout Builder', 'Post type general name', 'anwp-football-leagues-premium' ),
			'singular_name'      => _x( 'Layout Builder', 'Post type singular name', 'anwp-football-leagues-premium' ),
			'menu_name'          => _x( 'Layout Builder', 'Admin Menu text', 'anwp-football-leagues-premium' ),
			'name_admin_bar'     => _x( 'Layout', 'Add New on Toolbar', 'anwp-football-leagues-premium' ),
			'add_new'            => __( 'Add New Layout', 'anwp-football-leagues-premium' ),
			'add_new_item'       => __( 'Add New Layout', 'anwp-football-leagues-premium' ),
			'new_item'           => __( 'New Layout', 'anwp-football-leagues-premium' ),
			'edit_item'          => __( 'Edit Layout', 'anwp-football-leagues-premium' ),
			'view_item'          => __( 'View Layout', 'anwp-football-leagues-premium' ),
			'all_items'          => __( 'All Layouts', 'anwp-football-leagues-premium' ),
			'search_items'       => __( 'Search Layouts', 'anwp-football-leagues-premium' ),
			'not_found'          => __( 'No Layouts found.', 'anwp-football-leagues-premium' ),
			'not_found_in_trash' => __( 'No Layouts found in Trash.', 'anwp-football-leagues-premium' ),
		];

		$args = [
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'rewrite'            => false,
			'show_in_admin_bar'  => false,
			'menu_icon'          => 'dashicons-layout',
			'menu_position'      => 33,
			'show_ui'            => true,
			'supports'           => [ 'title' ],
		];

		register_post_type( 'anwp_fl_builder', $args );
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.8.15
	 */
	public function hooks() {

		// Admin Table filters
		add_filter( 'disable_months_dropdown', [ $this, 'disable_months_dropdown' ], 10, 2 );

		add_action( 'manage_anwp_fl_builder_posts_custom_column', [ $this, 'columns_display' ], 10, 2 );
		add_filter( 'manage_edit-anwp_fl_builder_columns', [ $this, 'columns' ] );

		// Init metabox
		add_action( 'load-post.php', [ $this, 'init_metaboxes' ] );
		add_action( 'load-post-new.php', [ $this, 'init_metaboxes' ] );

		// Remove metaboxes
		add_action( 'add_meta_boxes_anwp_fl_builder', [ $this, 'remove_metaboxes' ], 10, 1 );

		add_action( 'save_post_anwp_fl_builder', [ $this, 'save_metabox' ], 10, 2 );

		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );

		// Rendering Builder on frontend
		add_filter( 'anwpfl/template/load_default_template', [ $this, 'load_default_template' ], 10, 3 );
		add_action( 'anwpfl/template/load_alt_template', [ $this, 'render_builder_layout' ], 10, 2 );

		add_action( 'restrict_manage_posts', [ $this, 'add_more_filters' ] );
		add_filter( 'pre_get_posts', [ $this, 'handle_custom_filter' ] );

		add_filter( 'post_row_actions', [ $this, 'modify_quick_actions' ], 10, 2 );
		add_action( 'post_action_clone-builder', [ $this, 'process_clone_builder' ] );

		add_action( 'wp', [ $this, 'get_layout_builder_data' ], 99 );
		add_action( 'wp_head', [ $this, 'seo_output' ], 5 );

		/*
		|--------------------------------------------------------------------
		| SEO fix for other plugins
		|--------------------------------------------------------------------
		*/

		// Rank Math SEO
		add_filter( 'rank_math/frontend/title', [ $this, 'get_seo_title' ] );
		add_filter( 'rank_math/frontend/description', [ $this, 'get_seo_description' ] );

		// SEO SIMPLE PACK
		add_filter( 'ssp_output_title', [ $this, 'get_seo_title' ] );
		add_filter( 'ssp_output_description', [ $this, 'get_seo_description' ] );

		// Yoast SEO
		add_filter( 'wpseo_title', [ $this, 'get_seo_title' ] );
		add_filter( 'wpseo_opengraph_title', [ $this, 'get_seo_title' ] );
		add_filter( 'wpseo_metadesc', [ $this, 'get_seo_description' ] );
		add_filter( 'wpseo_opengraph_desc', [ $this, 'get_seo_description' ] );
	}

	/**
	 * Filters the array of row action links on the Pages list table.
	 *
	 * @param array $actions
	 * @param WP_Post $post
	 *
	 * @return array
	 * @since 0.12.6
	 */
	public function modify_quick_actions( $actions, $post ) {

		if ( 'anwp_fl_builder' === $post->post_type && current_user_can( 'edit_post', $post->ID ) ) {

			$clone_link               = admin_url( 'post.php?post=' . intval( $post->ID ) . '&action=clone-builder' );
			$actions['clone-builder'] = '<a href="' . esc_url( $clone_link ) . '">' . esc_html__( 'Clone', 'anwp-football-leagues' ) . '</a>';
		}

		return $actions;
	}

	/**
	 * Handle clone builder action.
	 *
	 * @param int $post_id
	 *
	 * @since 0.12.6
	 */
	public function process_clone_builder( $post_id ) {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$builder_id = wp_insert_post(
			[
				'post_type' => 'anwp_fl_builder',
			]
		);

		if ( $builder_id ) {

			$meta_fields_to_clone = [
				'_fl_builder_type',
				'_fl_fixed',
				'_fl_builder_top',
				'_fl_builder_bottom',
				'_fl_builder_tabs',
				'_fl_builder_competition_league',
				'_fl_builder_competition_type',
				'_fl_builder_competition_ids',
				'_fl_builder_match_ids',
				'_fl_builder_match_type',
				'_fl_builder_match_league',
			];

			/**
			 * Filter Standing Data to clone
			 *
			 * @param array $meta_fields_to_clone Clone data
			 * @param int   $post_id              Standing ID
			 * @param int   $builder_id           New Cloned Builder Layout ID
			 *
			 * @since 0.12.6
			 */
			$meta_fields_to_clone = apply_filters( 'anwpfl/builder/fields_to_clone', $meta_fields_to_clone, $post_id, $builder_id );

			foreach ( $meta_fields_to_clone as $meta_key ) {

				$meta_value = get_post_meta( $post_id, $meta_key, true );

				if ( '' !== $meta_value ) {
					$meta_value = maybe_unserialize( $meta_value );
					update_post_meta( $builder_id, $meta_key, wp_slash( $meta_value ) );
				}
			}

			update_post_meta( $builder_id, '_anwpfl_cloned', $post_id );

			// phpcs:ignore WordPress.Security.SafeRedirect
			if ( wp_redirect( admin_url( 'post.php?post=' . intval( $builder_id ) . '&action=edit' ) ) ) {
				exit;
			}
		}
	}

	/**
	 * Fires before the Filter button on the Posts and Pages list tables.
	 *
	 * The Filter button allows sorting by date and/or category on the
	 * Posts list table, and sorting by date on the Pages list table.
	 *
	 * @param string $post_type The post type slug.
	 */
	public function add_more_filters( $post_type ) {

		if ( 'anwp_fl_builder' === $post_type ) {

			ob_start();
			/*
			|--------------------------------------------------------------------
			| Live Status Filter
			|--------------------------------------------------------------------
			*/
			// phpcs:ignore WordPress.Security.NonceVerification
			$current_type_filter = empty( $_GET['_anwpfl_builder_type_filter'] ) ? '' : sanitize_key( $_GET['_anwpfl_builder_type_filter'] );
			?>
			<select name='_anwpfl_builder_type_filter' id='anwp_builder_type_filter' class='postform'>
				<option value=''><?php echo esc_html__( 'Layout Type', 'anwp-football-leagues-premium' ); ?></option>
				<option value="competition" <?php selected( 'competition', $current_type_filter ); ?>>- <?php echo esc_html__( 'Competition', 'anwp-football-leagues' ); ?></option>
				<option value="competition_multi" <?php selected( 'competition_multi', $current_type_filter ); ?>>- <?php echo esc_html__( 'Competition Multistage', 'anwp-football-leagues' ); ?></option>
				<option value="match" <?php selected( 'match', $current_type_filter ); ?>>- <?php echo esc_html__( 'Match', 'anwp-football-leagues' ); ?></option>
				<option value="match_live" <?php selected( 'match_live', $current_type_filter ); ?>>- <?php echo esc_html__( 'Match LIVE', 'anwp-football-leagues-premium' ); ?></option>
				<option value="player" <?php selected( 'player', $current_type_filter ); ?>>- <?php echo esc_html__( 'Player', 'anwp-football-leagues' ); ?></option>
				<option value="club" <?php selected( 'club', $current_type_filter ); ?>>- <?php echo esc_html__( 'Club', 'anwp-football-leagues' ); ?></option>
				<option value="staff" <?php selected( 'staff', $current_type_filter ); ?>>- <?php echo esc_html__( 'Staff', 'anwp-football-leagues' ); ?></option>
				<option value="referee" <?php selected( 'referee', $current_type_filter ); ?>>- <?php echo esc_html__( 'Referee', 'anwp-football-leagues' ); ?></option>
				<option value="stadium" <?php selected( 'stadium', $current_type_filter ); ?>>- <?php echo esc_html__( 'Stadium', 'anwp-football-leagues' ); ?></option>
			</select>
			<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo ob_get_clean();
		}
	}

	/**
	 * Handle custom filter.
	 *
	 * @param WP_Query $query
	 */
	public function handle_custom_filter( $query ) {
		global $post_type, $pagenow;

		// Check main query in admin
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( 'edit.php' !== $pagenow || 'anwp_fl_builder' !== $post_type ) {
			return;
		}

		$sub_query = [];

		// phpcs:ignore WordPress.Security.NonceVerification
		$type_filter = empty( $_GET['_anwpfl_builder_type_filter'] ) ? '' : sanitize_key( $_GET['_anwpfl_builder_type_filter'] );

		if ( $type_filter ) {
			$sub_query[] =
				[
					'key'   => '_fl_builder_type',
					'value' => $type_filter,
				];
		}

		/*
		|--------------------------------------------------------------------
		| Join All values to main query
		|--------------------------------------------------------------------
		*/
		if ( ! empty( $sub_query ) ) {
			$query->set(
				'meta_query',
				[
					array_merge( [ 'relation' => 'AND' ], $sub_query ),
				]
			);
		}
	}

	/**
	 * Registers admin columns to display.
	 *
	 * @since  0.8.15
	 *
	 * @param  array $columns Array of registered column names/labels.
	 * @return array          Modified array.
	 */
	public function columns( $columns ) {

		// Add new columns
		$new_columns = [
			'layout_info'          => esc_html__( 'Layout Info', 'anwp-football-leagues-premium' ),
			'anwp_layout_icon'     => '',
			'anwp_seo_title'       => esc_html__( 'SEO Title', 'anwp-football-leagues-premium' ),
			'anwp_seo_description' => esc_html__( 'SEO Description', 'anwp-football-leagues-premium' ),
		];

		// Merge old and new columns
		$columns = array_merge( $new_columns, $columns );

		// Change columns order
		$new_columns_order = [
			'cb',
			'title',
			'anwp_layout_icon',
			'layout_info',
			'anwp_seo_title',
			'anwp_seo_description',
		];

		$new_columns = [];

		foreach ( $new_columns_order as $c ) {

			if ( isset( $columns[ $c ] ) ) {
				$new_columns[ $c ] = $columns[ $c ];
			}
		}

		return $new_columns;
	}

	/**
	 * Handles admin column display.
	 *
	 * @param array   $column  Column currently being rendered.
	 * @param integer $post_id ID of post to display column for.
	 *
	 * @since  0.8.15
	 */
	public function columns_display( $column, $post_id ) {

		switch ( $column ) {
			case 'layout_info':
				$builder_type = get_post_meta( $post_id, '_fl_builder_type', true );

				echo '<strong>' . esc_html__( 'Type', 'anwp-football-leagues' ) . ':</strong> ' . esc_html( $builder_type ) . '<br/>';

				if ( ! empty( $this->types[ $builder_type ] ) && is_object( $this->types[ $builder_type ] ) ) {

					$builder_type_object = $this->types[ $builder_type ];

					if ( method_exists( $builder_type_object, 'admin_list_column_display' ) ) {
						$builder_type_object->admin_list_column_display( $post_id );
					}
				}
				break;

			case 'anwp_layout_icon':
				$builder_type = get_post_meta( $post_id, '_fl_builder_type', true );

				if ( ! empty( $this->types[ $builder_type ] ) && is_object( $this->types[ $builder_type ] ) ) {

					$builder_type_object = $this->types[ $builder_type ];

					if ( method_exists( $builder_type_object, 'admin_list_icon_display' ) ) {
						$builder_type_object->admin_list_icon_display( $post_id );
					}
				}
				break;

			case 'anwp_seo_title':
				echo esc_html( get_post_meta( $post_id, '_fl_builder_seo_title', true ) );

				break;

			case 'anwp_seo_description':
				echo esc_html( get_post_meta( $post_id, '_fl_builder_seo_description', true ) );

				break;
		}
	}

	/**
	 * Check builder or default template layout to load.
	 *
	 * @param bool    $load_default
	 * @param string  $post_type
	 * @param WP_Post $post
	 *
	 * @return bool
	 * @since 0.8.15
	 */
	public function load_default_template( $load_default, $post_type, $post ) {

		if ( false !== self::$layout_id ) {
			return ! self::$layout_id;
		}

		return $load_default;
	}

	/**
	 * Rendering builder layout.
	 *
	 * @param string  $post_type
	 * @param WP_Post $post
	 *
	 * @return mixed|void
	 * @since 0.8.15
	 */
	public function render_builder_layout( $post_type, $post ) {

		$layout_id          = self::$layout_id;
		$wrapper_classes    = '';
		$wrapper_attributes = '';
		$builder_type       = self::$layout_type;

		if ( ! $layout_id || ! $builder_type ) {
			return;
		}

		if ( ! empty( $this->types[ $builder_type ] ) && is_object( $this->types[ $builder_type ] ) ) {

			$builder_type_object = $this->types[ $builder_type ];

			if ( method_exists( $builder_type_object, 'get_builder_wrapper_classes' ) ) {
				$wrapper_classes = $builder_type_object->get_builder_wrapper_classes( $post );
			}

			if ( method_exists( $builder_type_object, 'get_builder_wrapper_attributes' ) ) {
				$wrapper_attributes = $builder_type_object->get_builder_wrapper_attributes( $post );
			}
		}

		ob_start();
		?>
		<div class="anwp-b-wrap anwp-fl-builder <?php echo esc_attr( $wrapper_classes ); ?>" <?php echo $wrapper_attributes; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php
			$top_layout = json_decode( get_post_meta( $layout_id, '_fl_builder_top', true ) );
			if ( ! empty( $top_layout ) && is_array( $top_layout ) ) :
				?>
				<div class="anwp-fl-builder__top anwp-row">
					<?php
					foreach ( $top_layout as $block ) :
						if ( ! empty( $block->alias ) ) :
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo $this->render_builder_block( $block->alias, $block, $post_type, $post->ID );
						endif;
					endforeach;
					?>
				</div>
			<?php endif; ?>

			<?php
			$tabs = json_decode( get_post_meta( $layout_id, '_fl_builder_tabs', true ) );
			if ( ! empty( $tabs ) && is_array( $tabs ) ) :
				?>
				<div class="anwp-fl-builder__tabs anwp-fl-builder-navbar mt-3">
					<div class="d-flex flex-wrap anwp-navbar__wrapper">
						<?php
						foreach ( $tabs as $tab_index => $tab ) :
							if ( ( isset( $tab->visible ) && ! $tab->visible ) || empty( $tab->title ) ) {
								continue;
							}

							echo '<div data-target="#anwp-fl-builder-tab-' . absint( $tab->id ) . '" class="anwp-navbar__item ml-0 mt-0 mr-1 mb-1 anwp-text-center anwp-flex-1 d-flex align-items-center justify-content-center ' . esc_attr( $tab_index ? '' : 'anwp-active-tab' ) . '"><span class="anwp-text-nowrap">' . esc_html( $tab->title ) . '</span></div>';
						endforeach;
						?>
					</div>
				</div>
				<div class="anwp-fl-builder__tabs-content-wrapper mt-3">
					<?php
					foreach ( $tabs as $tab_index => $tab ) :
						if ( ( isset( $tab->visible ) && ! $tab->visible ) || empty( $tab->title ) ) {
							continue;
						}

						echo '<div class="anwp-navbar__content anwp-fl-builder__tab anwp-row ' . esc_attr( $tab_index ? 'd-none' : '' ) . '" id="anwp-fl-builder-tab-' . absint( $tab->id ) . '">';

						foreach ( $tab->layout as $block ) :
							if ( ! empty( $block->alias ) ) :
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo $this->render_builder_block( $block->alias, $block, $post_type, $post->ID );
							endif;
						endforeach;

						echo '</div>';
					endforeach;
					?>
				</div>
			<?php endif; ?>
			<?php
			$bottom_layout = json_decode( get_post_meta( $layout_id, '_fl_builder_bottom', true ) );
			if ( ! empty( $bottom_layout ) && is_array( $bottom_layout ) ) :
				?>
				<div class="anwp-fl-builder__bottom anwp-row">
					<?php
					foreach ( $bottom_layout as $block ) :
						if ( ! empty( $block->alias ) ) :
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo $this->render_builder_block( $block->alias, $block, $post_type, $post->ID );
						endif;
					endforeach;
					?>
				</div>
			<?php endif; ?>
		</div>
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo ob_get_clean();
	}

	/**
	 * Remove term metaboxes.
	 *
	 * @since 0.8.15
	 */
	public function remove_metaboxes() {

		$post = get_post();

		if ( 'yes' !== get_post_meta( $post->ID, '_fl_fixed', true ) ) {
			remove_meta_box( 'submitdiv', 'anwp_fl_builder', 'side' );
		}
	}

	/**
	 * Filters whether to remove the 'Months' drop-down from the post list table.
	 *
	 * @param bool   $disable   Whether to disable the drop-down. Default false.
	 * @param string $post_type The post type.
	 *
	 * @return bool
	 * @since 0.8.15
	 */
	public function disable_months_dropdown( $disable, $post_type ) {

		return 'anwp_fl_builder' === $post_type ? true : $disable;
	}

	/**
	 * Meta box initialization.
	 *
	 * @since  0.1.0
	 */
	public function init_metaboxes() {
		add_action(
			'add_meta_boxes',
			function ( $post_type ) {
				if ( 'anwp_fl_builder' === $post_type ) {
					add_meta_box(
						'anwp_fl_builder',
						__( 'Layout Structure', 'anwp-football-leagues-premium' ),
						[ $this, 'render_metabox' ],
						$post_type,
						'normal',
						'high'
					);

					add_meta_box(
						'anwp_fl_builder_tutorials_metabox',
						esc_html__( 'Related Tutorials', 'anwp-football-leagues' ),
						[ $this, 'render_tutorials_metabox' ],
						$post_type,
						'side',
						'high'
					);
				}
			}
		);
	}

	/**
	 * Render the metabox to list related tutorials.
	 *
	 * @since 0.8.15
	 */
	public function render_tutorials_metabox() {

		ob_start();
		?>
		<p>
			<span class="dashicons dashicons-book-alt"></span>
			<a href="https://anwppro.userecho.com/knowledge-bases/2/articles/382-how-to-use-layout-builder" target="_blank">
				<?php echo esc_html__( 'How to Use Layout Builder', 'anwp-football-leagues-premium' ); ?>
			</a>
		</p>
		<p>
			<span class="dashicons dashicons-book-alt"></span>
			<a href="https://anwppro.userecho.com/knowledge-bases/2/articles/385-shortcode-block-layout-builder" target="_blank">
				<?php echo esc_html__( 'Shortcode Block', 'anwp-football-leagues-premium' ); ?>
			</a>
		</p>
		<p>
			<span class="dashicons dashicons-book-alt"></span>
			<a href="https://anwppro.userecho.com/knowledge-bases/2/articles/387-custom-classes-layout-builder" target="_blank">
				<?php echo esc_html__( 'Custom Classes', 'anwp-football-leagues-premium' ); ?>
			</a>
		</p>
		<?php

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo ob_get_clean();
	}

	/**
	 * Render Meta Box content for Layout Builder.
	 *
	 * @param WP_Post $post The post object.
	 *
	 * @since  0.8.15
	 */
	public function render_metabox( $post ) {

		// Add nonce for security and authentication.
		wp_nonce_field( 'anwp_save_metabox_' . $post->ID, 'anwp_metabox_nonce' );

		if ( 'yes' === get_post_meta( $post->ID, '_fl_fixed', true ) ) :

			// General Elements
			$general_elements = [
				[
					'name'     => 'Text',
					'group'    => 'Global',
					'alias'    => 'global_text',
					'header'   => '',
					'text'     => '',
					'width'    => '',
					'classes'  => '',
					'supports' => [ 'header', 'text', 'width' ],
				],
				[
					'name'     => 'Shortcode',
					'group'    => 'Global',
					'alias'    => 'global_shortcode',
					'header'   => '',
					'text'     => '',
					'width'    => '',
					'classes'  => '',
					'supports' => [ 'header', 'text', 'width' ],
				],
			];

			$builder_type         = get_post_meta( $post->ID, '_fl_builder_type', true );
			$type_elements        = [];
			$predefined_layouts   = [];
			$builder_variables    = [];
			$builder_conditionals = [];
			$builder_type_name    = '';
			$display_placeholders = '';
			$builder_tutorial     = '';

			if ( ! empty( $this->types[ $builder_type ] ) && is_object( $this->types[ $builder_type ] ) ) {
				$builder_type_object = $this->types[ $builder_type ];

				if ( method_exists( $builder_type_object, 'get_type_elements' ) ) {
					$type_elements = $builder_type_object->get_type_elements();
				}

				if ( method_exists( $builder_type_object, 'get_predefined_layouts' ) ) {
					$predefined_layouts = $builder_type_object->get_predefined_layouts();
				}

				if ( method_exists( $builder_type_object, 'get_builder_type_name' ) ) {
					$builder_type_name = $builder_type_object->get_builder_type_name();
				}

				if ( method_exists( $builder_type_object, 'get_dynamic_variables_info' ) ) {
					$display_placeholders = $builder_type_object->get_dynamic_variables_info( $post );
				}

				if ( method_exists( $builder_type_object, 'get_dynamic_variables' ) ) {
					$builder_variables = $builder_type_object->get_dynamic_variables( $post );
				}

				if ( method_exists( $builder_type_object, 'get_conditional_tags' ) ) {
					$builder_conditionals = $builder_type_object->get_conditional_tags( $post );
				}

				if ( method_exists( $builder_type_object, 'get_tutorial_link' ) ) {
					$builder_tutorial = $builder_type_object->get_tutorial_link( $post );
				}
			}

			$builder_elements = array_merge( $general_elements, $type_elements );

			$builder_l10n = [
				'all_leagues'                                 => __( 'All Leagues', 'anwp-football-leagues' ),
				'comma_separated_list_of_ids'                 => __( 'comma separated list of IDs', 'anwp-football-leagues-premium' ),
				'competition_ids'                             => __( 'Competition IDs', 'anwp-football-leagues-premium' ),
				'competition_type'                            => __( 'Competition Type', 'anwp-football-leagues' ),
				'display_options'                             => __( 'Display Options', 'anwp-football-leagues' ),
				'fill_display_options_section'                => __( 'Fill "Display Options" section to make this layout visible', 'anwp-football-leagues-premium' ),
				'finished'                                    => __( 'Finished', 'anwp-football-leagues-premium' ),
				'knockout'                                    => __( 'Knockout', 'anwp-football-leagues-premium' ),
				'league'                                      => __( 'League', 'anwp-football-leagues' ),
				'match_ids'                                   => __( 'Match IDs', 'anwp-football-leagues-premium' ),
				'optional'                                    => __( 'optional', 'anwp-football-leagues-premium' ),
				'optional_the_highest_priority'               => __( 'Optional. The highest priority. If set, all other fields will be ignored', 'anwp-football-leagues-premium' ),
				'round-robin'                                 => __( 'Round Robin', 'anwp-football-leagues-premium' ),
				'select_league'                               => __( 'select league', 'anwp-football-leagues' ),
				'seo'                                         => __( 'SEO', 'anwp-football-leagues-premium' ),
				'status'                                      => __( 'Status', 'anwp-football-leagues' ),
				'status_league'                               => __( 'Game Status & League', 'anwp-football-leagues-premium' ),
				'this_layout_will_be_applied_to_all_clubs'    => __( 'This layout will be applied to all clubs', 'anwp-football-leagues-premium' ),
				'this_layout_will_be_applied_to_all_players'  => __( 'This layout will be applied to all players', 'anwp-football-leagues-premium' ),
				'this_layout_will_be_applied_to_all_referees' => __( 'This layout will be applied to all referees', 'anwp-football-leagues-premium' ),
				'this_layout_will_be_applied_to_all_stadiums' => __( 'This layout will be applied to all stadiums', 'anwp-football-leagues-premium' ),
				'this_layout_will_be_applied_to_all_staff'    => __( 'This layout will be applied to all staff', 'anwp-football-leagues-premium' ),
				'this_layout_will_be_applied_to_all_live'     => __( 'This layout will be applied to all LIVE games', 'anwp-football-leagues-premium' ),
				'upcoming'                                    => __( 'Upcoming', 'anwp-football-leagues-premium' ),
			];

			$builder_options = [
				'leagues' => anwp_football_leagues()->league->get_leagues_list(),
			];

			$builder_display_options = [
				'_fl_builder_competition_ids'    => get_post_meta( $post->ID, '_fl_builder_competition_ids', true ),
				'_fl_builder_competition_league' => get_post_meta( $post->ID, '_fl_builder_competition_league', true ),
				'_fl_builder_competition_type'   => get_post_meta( $post->ID, '_fl_builder_competition_type', true ),
				'_fl_builder_match_type'         => get_post_meta( $post->ID, '_fl_builder_match_type', true ),
				'_fl_builder_match_ids'          => get_post_meta( $post->ID, '_fl_builder_match_ids', true ),
				'_fl_builder_match_league'       => get_post_meta( $post->ID, '_fl_builder_match_league', true ),
			];
			?>
			<script type="text/javascript">
				var _flProBlockOptions             = <?php echo wp_json_encode( $builder_elements ); ?>;
				var _flProBuilderPredefinedLayouts = <?php echo wp_json_encode( $predefined_layouts ); ?>;
				var _flProBuilderTypeName          = '<?php echo esc_html( $builder_type_name ); ?>';
				var _flProBuilderOptions           = <?php echo wp_json_encode( $builder_options ); ?>;
				var _flProBuilderType              = '<?php echo esc_html( $builder_type ); ?>';
				var _flProBuilderTutorial          = '<?php echo esc_html( $builder_tutorial ); ?>';
				var _flProBuilderVariables         = <?php echo wp_json_encode( $builder_variables ); ?>;
				var _flProBuilderConditionals      = <?php echo wp_json_encode( $builder_conditionals ); ?>;
				var _flProBuilderL10n              = <?php echo wp_json_encode( $builder_l10n ); ?>;
				var _flProBuilderDisplayOptions    = <?php echo wp_json_encode( $builder_display_options ); ?>;
				var _flProBuilderSeoTitle          = '<?php echo esc_js( get_post_meta( $post->ID, '_fl_builder_seo_title', true ) ); ?>';
				var _flProBuilderSeoDescription    = '<?php echo esc_js( get_post_meta( $post->ID, '_fl_builder_seo_description', true ) ); ?>';
			</script>
			<div class="anwp-admin-metabox anwp-b-wrap">

				<div id="fl-app-layout-builder"></div>

				<div class="anwp-b-wrap">
					<input class="button button-primary button-large mt-0 px-5" type="submit" value="<?php esc_html_e( 'Save', 'anwp-football-leagues-premium' ); ?>">
				</div>

				<?php
				if ( $display_placeholders ) {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo $display_placeholders;
				}
				?>

				<input type="hidden" value="yes" name="_fl_fixed">

				<a href="#anwp-fl-vue-shortcode-builder-modaal" id="anwpfl-shortcode-modal-vue-builder-bump" style="display: none;"></a>
				<div id="anwp-fl-vue-shortcode-builder-modaal" style="display: none;">
					<div class="anwpfl-shortcode-modal__content">
						<?php AnWP_Football_Leagues::include_file( 'admin/views/shortcodes-builder' ); ?>
					</div>
					<div class="anwpfl-shortcode-modal__footer d-flex">
						<button id="anwp-fl-vue-builder-modaal__cancel" class="button ml-auto"><?php echo esc_html__( 'Close', 'anwp-football-leagues' ); ?></button>
						<button id="anwp-fl-vue-builder-modaal__copy" class="button button-primary ml-3"><?php echo esc_html__( 'Copy and Close', 'anwp-football-leagues-premium' ); ?></button>
					</div>
				</div>

				<script>
					( function( $ ) {

						var $modalLink = $( '#anwpfl-shortcode-modal-vue-builder-bump' );

						$modalLink.modaal(
							{
								content_source: '#anwp-fl-vue-shortcode-builder-modaal',
								custom_class: 'anwpfl-shortcode-modal',
								hide_close: true,
								animation: 'none'
							}
						);

						$( '#anwp-fl-vue-builder-modaal__cancel' ).on( 'click', function( e ) {
							e.preventDefault();
							$modalLink.modaal( 'close' );
						} );

						$( '#anwp-fl-vue-builder-modaal__copy' ).on( 'click', function( e ) {
							e.preventDefault();
							$( '#anwp-shortcode-builder__copy' ).click();
							$modalLink.modaal( 'close' );
						} );
					} )( jQuery );
				</script>
			</div>
		<?php else : ?>
			<div class="anwp-admin-metabox anwp-b-wrap">
				<div class="anwp-admin-block mb-3">
					<div class="anwp-admin-block__header"><?php echo esc_html__( 'Select Layout Type', 'anwp-football-leagues-premium' ); ?></div>
					<div class="anwp-admin-block__content">
						<select name="_fl_builder_type" class="postform">
							<?php
							foreach ( $this->types as $builder_type_object ) :
								if ( is_object( $builder_type_object ) && method_exists( $builder_type_object, 'get_builder_type_option' ) ) :
									// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									echo $builder_type_object->get_builder_type_option();
								endif;
							endforeach;
							?>
						</select>
					</div>
					<div class="anwp-admin-block__footer">
						<button class="button button-primary button-large" name="select-layout-type" type="submit" value="yes"><?php esc_html_e( 'Select Type & Continue', 'anwp-football-leagues-premium' ); ?></button>
					</div>
				</div>
			</div>
			<?php
		endif;
	}

	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int     $post_id The ID of the post being saved.
	 * @param WP_Post $post
	 *
	 * @since  0.8.15
	 * @return bool|int
	 */
	public function save_metabox( $post_id, $post ) {

		/*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST['anwp_metabox_nonce'] ) ) {
			return $post_id;
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['anwp_metabox_nonce'], 'anwp_save_metabox_' . $post_id ) ) {
			return $post_id;
		}

		// Check post type
		if ( 'anwp_fl_builder' !== $_POST['post_type'] ) {
			return $post_id;
		}

		/*
		 * If this is an autosave, our form has not been submitted,
		 * so we don't want to do anything.
		 */
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Check the user's permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		// check if there was a multisite switch before
		if ( is_multisite() && ms_is_switched() ) {
			return $post_id;
		}

		/* OK, it's safe for us to save the data now. */

		/** ---------------------------------------
		 * Save Data
		 * ---------------------------------------*/

		$fixed = empty( $_POST['_fl_fixed'] ) ? '' : sanitize_key( $_POST['_fl_fixed'] );

		if ( 'yes' === $fixed ) {

			update_post_meta( $post_id, '_fl_fixed', 'yes' );

			// Prepare data & Encode with some WP sanitization
			$top    = isset( $_POST['_fl_builder_top'] ) ? wp_json_encode( json_decode( stripslashes( $_POST['_fl_builder_top'] ) ) ) : wp_json_encode( [] );
			$bottom = isset( $_POST['_fl_builder_bottom'] ) ? wp_json_encode( json_decode( stripslashes( $_POST['_fl_builder_bottom'] ) ) ) : wp_json_encode( [] );
			$tabs   = isset( $_POST['_fl_builder_tabs'] ) ? wp_json_encode( json_decode( stripslashes( $_POST['_fl_builder_tabs'] ) ) ) : wp_json_encode( [] );

			if ( $top ) {
				update_post_meta( $post_id, '_fl_builder_top', wp_slash( $top ) );
			}

			if ( $bottom ) {
				update_post_meta( $post_id, '_fl_builder_bottom', wp_slash( $bottom ) );
			}

			if ( $tabs ) {
				update_post_meta( $post_id, '_fl_builder_tabs', wp_slash( $tabs ) );
			}

			/*
			|--------------------------------------------------------------------
			| Save SEO
			|--------------------------------------------------------------------
			*/
			update_post_meta( $post_id, '_fl_builder_seo_title', empty( $_POST['_fl_builder_seo_title'] ) ? '' : sanitize_text_field( $_POST['_fl_builder_seo_title'] ) );
			update_post_meta( $post_id, '_fl_builder_seo_description', empty( $_POST['_fl_builder_seo_description'] ) ? '' : sanitize_textarea_field( $_POST['_fl_builder_seo_description'] ) );

			/*
			|--------------------------------------------------------------------
			| Save Display Options
			|--------------------------------------------------------------------
			*/
			$builder_type         = get_post_meta( $post_id, '_fl_builder_type', true );
			$post_display_options = wp_parse_args(
				wp_unslash( $_POST ),
				[
					'_fl_builder_competition_ids'    => '',
					'_fl_builder_competition_league' => '',
					'_fl_builder_competition_type'   => '',
					'_fl_builder_match_ids'          => '',
					'_fl_builder_match_league'       => '',
					'_fl_builder_match_type'         => '',
				]
			);

			switch ( $builder_type ) {
				case 'competition_multi':
					update_post_meta( $post_id, '_fl_builder_competition_ids', sanitize_text_field( $post_display_options['_fl_builder_competition_ids'] ) );
					update_post_meta( $post_id, '_fl_builder_competition_league', sanitize_text_field( $post_display_options['_fl_builder_competition_league'] ) );

					break;

				case 'competition':
					update_post_meta( $post_id, '_fl_builder_competition_ids', sanitize_text_field( $post_display_options['_fl_builder_competition_ids'] ) );
					update_post_meta( $post_id, '_fl_builder_competition_type', $post_display_options['_fl_builder_competition_type'] ? explode( ',', sanitize_text_field( $post_display_options['_fl_builder_competition_type'] ) ) : [] );

					break;

				case 'match':
					update_post_meta( $post_id, '_fl_builder_match_ids', sanitize_text_field( $post_display_options['_fl_builder_match_ids'] ) );
					update_post_meta( $post_id, '_fl_builder_match_league', sanitize_text_field( $post_display_options['_fl_builder_match_league'] ) );
					update_post_meta( $post_id, '_fl_builder_match_type', $post_display_options['_fl_builder_match_type'] ? explode( ',', sanitize_text_field( $post_display_options['_fl_builder_match_type'] ) ) : [] );

					break;
			}
		} else {

			if ( isset( $_POST['select-layout-type'] ) && 'yes' === $_POST['select-layout-type'] ) {
				update_post_meta( $post_id, '_fl_fixed', 'yes' );

				$update_data = [
					'ID'         => $post_id,
					'post_title' => empty( $_POST['post_title'] ) ? 'Layout #' . absint( $post_id ) : sanitize_text_field( $_POST['post_title'] ),
				];

				if ( 'publish' !== $post->post_status ) {
					$update_data['post_status'] = 'publish';
				}

				remove_action( 'save_post_anwp_fl_builder', [ $this, 'save_metabox' ] );

				wp_update_post( $update_data );

				// re-hook this function
				add_action( 'save_post_anwp_fl_builder', [ $this, 'save_metabox' ] );
			}

			// Save type option
			$builder_type = isset( $_POST['_fl_builder_type'] ) ? sanitize_text_field( $_POST['_fl_builder_type'] ) : '';
			update_post_meta( $post_id, '_fl_builder_type', $builder_type );
		}

		return $post_id;
	}

	/**
	 * Load admin scripts and styles
	 *
	 * @param string $hook_suffix The current admin page.
	 *
	 * @since 0.8.15
	 */
	public function admin_enqueue_scripts( $hook_suffix ) {

		$current_screen = get_current_screen();

		if ( in_array( $hook_suffix, [ 'post.php', 'post-new.php' ], true ) && 'anwp_fl_builder' === $current_screen->id ) {

			$post_id = get_the_ID();

			if ( 'yes' === get_post_meta( $post_id, '_fl_fixed', true ) ) {

				$data = [
					'top'    => get_post_meta( $post_id, '_fl_builder_top', true ),
					'bottom' => get_post_meta( $post_id, '_fl_builder_bottom', true ),
					'tabs'   => get_post_meta( $post_id, '_fl_builder_tabs', true ),
				];

				wp_localize_script( 'anwpfl_premium_admin_vue', '_flBuilderData', $data );
			}
		}
	}

	/**
	 * Render builder block.
	 *
	 * @param $block_alias
	 * @param $block
	 * @param $post_type
	 * @param $post_id
	 */
	public function render_builder_block( $block_alias, $block, $post_type, $post_id ) {

		$builder_type = $this->get_builder_type_by_post_type( $post_type, $post_id );

		if ( ! empty( $this->types[ $builder_type ] ) && is_object( $this->types[ $builder_type ] ) ) {
			$builder_type_object = $this->types[ $builder_type ];
		} else {
			$builder_type_object = null;
		}

		if ( $builder_type_object && method_exists( $builder_type_object, 'maybe_render_block' ) && ! $builder_type_object->maybe_render_block( $block, $post_id ) ) {
			return;
		}

		$block = (object) wp_parse_args(
			$block,
			[
				'text'          => '',
				'width'         => '',
				'classes'       => '',
				'header'        => '',
				'margin_top'    => '',
				'margin_bottom' => '',
				'group_n'       => '',
				'stage_n'       => '',
			]
		);

		if ( '' !== $block->margin_top ) {
			$block->classes = esc_attr( $block->margin_top ) . ' ' . $block->classes;
		}

		if ( '' !== $block->margin_bottom ) {
			$block->classes = esc_attr( $block->margin_bottom ) . ' ' . $block->classes;
		}

		/*
		|--------------------------------------------------------------------
		| Render blocks
		|--------------------------------------------------------------------
		*/
		if ( 'global_text' === $block_alias ) {
			$classes = $this->get_builder_width_class( $block->width ) . ' ' . $block->classes;

			// header
			$header = '';
			if ( trim( $block->header ) ) {
				$header = '<div class="anwp-fl-block-header anwp-text-xl">' . esc_html( $block->header ) . '</div>';
			}

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<div class="anwp-fl-builder-block ' . esc_attr( $classes ) . '">' . $header . '<div class="anwp-block-content">' . do_shortcode( $block->text ) . '</div></div>';

		} elseif ( 'global_shortcode' === $block_alias ) {
			$classes = $this->get_builder_width_class( $block->width ) . ' ' . $block->classes;

			// header
			$header = '';
			if ( trim( $block->header ) ) {
				$header = '<div class="anwp-fl-block-header anwp-text-xl">' . esc_html( $block->header ) . '</div>';
			}

			if ( $builder_type_object && method_exists( $builder_type_object, 'replace_dynamic_variables' ) ) {
				$block->text = $builder_type_object->replace_dynamic_variables( $block->text, $post_id );
			}

			ob_start();
			echo do_shortcode( $block->text );
			$shortcode_output = ob_get_clean();

			if ( ! empty( $shortcode_output ) ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<div class="anwp-fl-builder-block ' . esc_attr( $classes ) . '">' . $header . '<div class="anwp-block-content">' . $shortcode_output . '</div></div>';
			}
		} elseif ( $builder_type_object ) {
			$render_method = 'render_' . sanitize_key( $block_alias );

			if ( method_exists( $builder_type_object, $render_method ) ) {
				$builder_type_object->{$render_method}( $block, $post_id );
			}
		}
	}

	/**
	 * Get builder block width class.
	 *
	 * @param $width
	 *
	 * @return string
	 * @since 0.8.15
	 */
	public function get_builder_width_class( $width ) {

		$class = 'anwp-col-12';

		switch ( $width ) {
			case 'col-6':
				$class = 'anwp-col-md-6';
				break;

			case 'col-4':
				$class = 'anwp-col-md-6 anwp-col-xl-4';
				break;
		}

		return $class;
	}

	/**
	 * Get builder type by post type.
	 *
	 * @param string $post_type
	 * @param int    $post_id
	 *
	 * @return string
	 * @since 0.11.2
	 */
	public static function get_builder_type_by_post_type( string $post_type, int $post_id ): string {

		$builder_type = str_ireplace( 'anwp_', '', $post_type );

		if ( 'competition' === $builder_type && get_post_meta( $post_id, '_anwpfl_multistage', true ) ) {
			$builder_type = 'competition_multi';
		}

		if ( 'match' === $builder_type ) {
			$game_data = anwp_fl()->match->get_game_data( $post_id );

			if ( ! empty( $game_data ) && ! absint( $game_data['finished'] ) ) {
				if ( ! empty( get_post_meta( $post_id, '_anwpfl_live_status', true ) ) || anwp_fl_pro()->live->is_api_game_active( $post_id ) ) {

					$layouts = get_posts(
						[
							'post_type'   => 'anwp_fl_builder',
							'numberposts' => - 1,
							'meta_key'    => '_fl_builder_type',
							'meta_value'  => 'match_live',
						]
					);

					if ( ! empty( $layouts ) ) {
						$builder_type = 'match_live';
					}
				}
			}
		}

		return $builder_type;
	}

	/**
	 * Get Builder Layout Id
	 *
	 * @return void
	 * @since 0.14.7
	 */
	public function get_layout_builder_data() {
		global $post;

		self::$layout_id = 0;

		if ( $post && in_array( $post->post_type, anwp_football_leagues()->get_post_types(), true ) && is_singular() && is_main_query() && ! post_password_required() ) {
			self::$layout_type = $this->get_builder_type_by_post_type( $post->post_type, $post->ID );

			if ( ! empty( $this->types[ self::$layout_type ] ) && is_object( $this->types[ self::$layout_type ] ) ) {

				$builder_type_object = $this->types[ self::$layout_type ];

				if ( method_exists( $builder_type_object, 'get_builder_layout_id' ) ) {
					$maybe_layout_id = $builder_type_object->get_builder_layout_id( $post );

					if ( absint( $maybe_layout_id ) ) {
						self::$layout_id = absint( $maybe_layout_id );
						$this->generate_seo_title();
						$this->generate_seo_description();
					}
				}
			}
		}
	}

	/**
	 * Generate SEO title
	 *
	 * @return void
	 * @since 0.14.7
	 */
	public function generate_seo_title() {

		global $post;

		$string_to_parse = get_post_meta( self::$layout_id, '_fl_builder_seo_title', true );

		if ( $string_to_parse ) {

			$builder_type_object = $this->types[ self::$layout_type ];

			if ( method_exists( $builder_type_object, 'replace_dynamic_variables' ) ) {
				self::$layout_seo_title = $builder_type_object->replace_dynamic_variables( $string_to_parse, $post->ID );
			}

			remove_action( 'wp_head', '_wp_render_title_tag', 1 );
		}
	}

	/**
	 * Generate SEO description
	 *
	 * @return void
	 * @since 0.14.7
	 */
	public function generate_seo_description() {

		global $post;

		$string_to_parse = get_post_meta( self::$layout_id, '_fl_builder_seo_description', true );

		if ( $string_to_parse ) {

			$builder_type_object = $this->types[ self::$layout_type ];

			if ( method_exists( $builder_type_object, 'replace_dynamic_variables' ) ) {
				self::$layout_seo_description = $builder_type_object->replace_dynamic_variables( $string_to_parse, $post->ID );
			}
		}
	}

	/**
	 * Get SEO title
	 *
	 * @return string
	 * @since 0.14.7
	 */
	public function get_seo_title( $title = '' ) {
		return self::$layout_seo_title ? : $title;
	}

	/**
	 * Get SEO description
	 *
	 * @return string
	 * @since 0.14.7
	 */
	public function get_seo_description( $description = '' ) {
		return self::$layout_seo_description ? : $description;
	}

	/**
	 * SEO Output
	 *
	 * @return void
	 * @since 0.14.7
	 */
	public function seo_output() {

		if ( empty( self::$layout_id ) ) {
			return;
		}

		/*
		|--------------------------------------------------------------------
		| Ignore if other SEO plugins installed
		|--------------------------------------------------------------------
		*/
		if ( defined( 'WPSEO_VERSION' ) || defined( 'SSP_VERSION' ) || defined( 'RANK_MATH_VERSION' ) ) {
			return;
		}

		/*
		|--------------------------------------------------------------------
		| Start rendering output
		|--------------------------------------------------------------------
		*/
		echo PHP_EOL . '<!-- AnWP FL SEO -->' . PHP_EOL;

		if ( ! empty( self::$layout_seo_title ) ) {
			echo '<title>' . esc_html( self::$layout_seo_title ) . '</title>' . PHP_EOL;
			echo '<meta property="og:title" content="' . esc_attr( self::$layout_seo_title ) . '">' . PHP_EOL;
		}

		if ( ! empty( self::$layout_seo_description ) ) {
			echo '<meta name="description" content="' . esc_attr( self::$layout_seo_description ) . '">' . PHP_EOL;
			echo '<meta property="og:description" content="' . esc_attr( self::$layout_seo_description ) . '">' . PHP_EOL;
		}

		echo '<!-- / AnWP FL SEO -->' . PHP_EOL . PHP_EOL;
	}
}
