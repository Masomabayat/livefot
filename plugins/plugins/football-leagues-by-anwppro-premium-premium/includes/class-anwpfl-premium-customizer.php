<?php
/**
 * AnWP Football Leagues Premium :: Customizer.
 *
 * @package AnWP_Football_Leagues_Premium
 */

class AnWPFL_Premium_Customizer {

	/**
	 * Parent plugin class.
	 *
	 * @var AnWP_Football_Leagues_Premium
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 * Register Custom Post Types.
	 *
	 * See documentation in CPT_Core, and in wp-includes/post.php.
	 *
	 * @param  AnWP_Football_Leagues_Premium $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;

		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 */
	public function hooks() {
		add_filter( 'anwpfl/customizer/plugin-classes', [ $this, 'add_customizer_classes' ] );

		add_action( 'customize_register', [ $this, 'register_customizer_settings' ] );
	}

	/**
	 * Get Customizer saved option
	 *
	 * @param string $section
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function get_value( $section, $key, $default = '' ) {

		if ( class_exists( 'AnWPFL_Customizer' ) ) {
			return anwp_football_leagues()->customizer->get_value( $section, $key, $default );
		}

		return $default;
	}

	/**
	 * Add plugin Customizer classes
	 *
	 * @param $classes
	 *
	 * @return mixed
	 */
	public function add_customizer_classes( $classes ) {

		$premium_classes = [
			'match-card__timer-flip',
			'standing__conference-title',
			'club-form__item-pro',
			'transfers-list-club',
			'transfers-list-club__type',
			'transfers-list-club__player',
			'transfers-list-club__club-out',
			'transfers-list-club__dob',
			'transfers-list-club__date',
			'transfers-list-club__fee',
			'transfers-list-club__data',
			'transfers-list-club__player',
			'transfers-list-club__player-photo',
			'transfers-list-club__player-name',
			'transfers-list-club__player-position',
			'transfers-list-club__player-dob',
			'transfers-list-club__rumour',
			'transfers-list-club__club-out-logo',
			'transfers-list-club__club-in-logo',
			'transfers-list-club__club-in',
			'transfers-list-competition__club-title',
			'transfers-list-competition',
			'transfers-list-competition__type',
			'transfers-list-competition__player',
			'transfers-list-competition__club-out',
			'transfers-list-competition__dob',
			'transfers-list-competition__date',
			'transfers-list-competition__fee',
			'transfers-list-competition__data',
			'transfers-list-competition__player-name',
			'transfers-list-competition__player-photo',
			'transfers-list-competition__player-position',
			'transfers-list-competition__club-out-logo',
			'transfers-list-competition__club-in',
			'transfers-list-competition__rumour',
			'transfers-list-competition__club-in-logo',
			'transfers-list-competition-compact',
			'transfers-list-player',
			'transfers-list-player__club-out',
			'transfers-list-player__club-in',
			'transfers-list-player__date',
			'transfers-list-player__fee',
			'transfers-list-player__data',
			'transfers-list-player__club-out-logo',
			'transfers-list-player__club-in-logo',
			'transfers-list-player__rumour',
			'anwpfl-suspension-risk',
			'anwpfl-suspension-risk__grid',
			'anwpfl-suspension-risk__team',
			'anwpfl-suspension-risk__team-logo',
			'anwpfl-suspension-risk__team-name',
			'anwpfl-suspension-risk__player',
			'anwpfl-suspension-risk__player-photo',
			'anwpfl-suspension-risk__player-data',
			'anwpfl-suspension-risk__player-name',
			'anwpfl-suspension-risk__player-position',
			'anwpfl-suspension-risk__cards',
			'player-stats-panel',
			'player-stats-panel__header',
			'player-stats-panel__label',
			'player-stats-panel__values',
			'player-stats-panel__value-major',
			'player-stats-panel__value-minor',
			'player-birthday-card-wide',
			'player-birthday-card-wide__age-wrapper',
			'player-birthday-card-wide__years',
			'player-birthday-card-wide__age',
			'player-birthday-card-wide__photo-wrapper',
			'player-birthday-card-wide__photo',
			'player-birthday-card-wide__meta',
			'player-birthday-card-wide__name',
			'player-birthday-card-wide__date',
			'player-birthday-card-wide__date-text',
			'player-birthday-card-wide__club-wrapper',
			'player-birthday-card-wide__position',
			'player-birthday-card-wide-slim',
			'anwp-fl-calendar-slider',
			'anwp-fl-calendar-slider__swiper-container-outer',
			'anwp-fl-calendar-slider__swiper-container',
			'anwp-fl-calendar-slider__swiper-slide',
			'anwp-fl-calendar-slider__day',
			'anwp-fl-calendar-slider__month-text',
			'anwp-fl-calendar-slider__day-text',
			'anwp-match-prediction-wrapper',
			'match-commentary',
			'match-commentary__wrapper-outer',
			'match-commentary__wrapper',
			'match-commentary__wrapper-shadow',
			'match-commentary__row',
			'match-commentary__block',
			'match-commentary__block--home',
			'match-commentary__block-header',
			'match-commentary__event-icon--inner',
			'match-commentary__event-name',
			'match-commentary__scores',
			'match-commentary__minute',
			'match-commentary__block-sub-header',
			'match-commentary__block-text',
			'match-commentary__event-icon',
			'match-commentary__block--away',
			'match-formation',
			'match-formation__field',
			'match-formation__line',
			'match-formation__player',
			'match-formation__player-name',
			'match-formation__player-number',
			'anwp-fl-prediction-advice',
			'anwp-fl-prediction-advice__header',
			'anwp-fl-prediction-advice__text',
			'anwp-fl-prediction-percent',
			'anwp-fl-prediction-percent__stats',
			'anwp-fl-prediction-percent__text',
			'match-scoreboard',
			'match-scoreboard__inner',
			'match-scoreboard__header',
			'match-scoreboard__header-line',
			'match-scoreboard__kickoff',
			'match-scoreboard__main',
			'match-scoreboard__club-wrapper',
			'match-scoreboard__club-title',
			'match-scoreboard__scores',
			'match-scoreboard__score-number',
			'match-scoreboard__score-separator',
			'match-scoreboard__text-result',
			'match-scoreboard__events',
			'match-scoreboard__goal-wrapper',
			'match-scoreboard__goal-icon',
			'match-scoreboard__goal-player',
			'match-scoreboard__goal-minute',
			'match-scoreboard__countdown',
			'match-scoreboard__footer',
			'match-scoreboard__footer-line',
			'anwp-match-prediction-wrapper',
			'match-stats__stat-wrapper',
			'team-stats__title',
			'team-stats__value',
			'match-stats__stat-bar-inner',
			'match-timeline',
			'match-timeline__club_logos',
			'match-timeline__inner',
			'match-timeline__progress',
			'match-timeline__progress-filled',
			'match-timeline__item',
			'anwp-match-widget-shots',
			'anwp-match-widget-shots__block-outer',
			'anwp-match-widget-shots__block-off',
			'anwp-match-widget-shots__value',
			'anwp-match-widget-shots__bg',
			'club__trophies',
			'club-trophy',
			'club-trophy__number',
			'club-trophy__text',
			'club-trophy__img',
			'odds-wrapper',
			'odds__selector',
			'odds__updated-wrapper',
			'odds__updated-text',
			'odds__updated-date',
			'odds__table',
			'stat-players',
			'stat-players__first_photo',
			'stat-players__first_photo_img',
			'stat-players__first_data',
			'stat-players__first-place',
			'stat-players__first-clubs',
			'stat-players__first-name',
			'stat-players__first-stat',
			'stat-players__first-gp',
			'stat-players__place',
			'stat-players__clubs',
			'stat-players__name',
			'stat-players__stat',
			'stat-players__gp',
			'anwp-modaal-stat-players-list',
			'stat-players__photo',
			'stat-players__photo_img',
			'stat-players__club-logo',
			'stat-players__first-club-logo',
			'stat-players__player-wrapper',
			'stat-players__first-player-wrapper',
			'referee-stats',
			'referee-stats__games-num-wrapper ',
			'referee-stats__games-num',
			'referee-stats__wrapper',
			'referee-stats__item',
			'referee-stats__stat',
			'referee-stats__value',
			'referee-stats__pg',
			'referee-stats__pg-text',
			'referee-stats__notes',
			'anwp-fl-player-stats-shortcode',
			'player-stats-pro',
			'player-stats-pro__season-wrapper',
			'player-stats-pro__season',
			'player-stats-pro__wrapper',
			'player-stats-pro__item',
			'player-stats-pro__stat',
			'player-stats-pro__value',
			'player-stats-pro__pg',
			'player-stats-pro__pg-text',
			'player-stats-pro__notes',
			'anwp-fl-matches-scoreboard__swiper-button-prev',
			'anwp-fl-matches-scoreboard__swiper-button-next',
			'anwp-btn-group',
			'anwp-btn-group__btn',
			'anwp-fl-pro-game-countdown',
			'anwp-fl-pro-game-countdown--big',
			'anwp-fl-pro-game-countdown__item',
			'anwp-fl-pro-game-countdown__days',
			'anwp-fl-pro-game-countdown__hours',
			'anwp-fl-pro-game-countdown__minutes',
			'anwp-fl-pro-game-countdown__seconds',
			'anwp-fl-pro-game-countdown__svg',
			'anwp-fl-pro-game-countdown__circle',
			'anwp-fl-pro-game-countdown__path-elapsed',
			'anwp-fl-pro-game-countdown__path-remaining',
			'anwp-fl-pro-game-countdown__label-wrapper',
			'anwp-fl-pro-game-countdown__label',
			'anwp-fl-pro-game-countdown__value',
		];

		return array_merge( $classes, $premium_classes );
	}

	/**
	 * Register Customizer settings
	 */
	public function register_customizer_settings( $wp_customize ) {

		/*
		|--------------------------------------------------------------------
		| countdown_layout
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[match][countdown_layout]',
			[
				'default' => 'modern',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[match][countdown_layout]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Countdown Type', 'anwp-football-leagues-premium' ),
				'section'  => 'fl_match',
				'settings' => 'anwp-fl-customizer[match][countdown_layout]',
				'choices'  => [
					'classic' => __( 'Classic', 'anwp-football-leagues-premium' ),
					'modern'  => __( 'Modern', 'anwp-football-leagues-premium' ),
				],
			]
		);

		/*
		|--------------------------------------------------------------------
		| use_abbr_in_standing_mini
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[standing][show_standing_arrows]',
			[
				'default' => 'yes',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[standing][show_standing_arrows]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Show arrows (dynamic of ranking change)', 'anwp-football-leagues-premium' ),
				'section'  => 'fl_standing',
				'settings' => 'anwp-fl-customizer[standing][show_standing_arrows]',
				'choices'  => [
					'no'  => __( 'No', 'anwp-football-leagues' ),
					'yes' => __( 'Yes', 'anwp-football-leagues' ),
				],
			]
		);

		/*
		|--------------------------------------------------------------------
		| show_home_away
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[standing][show_home_away]',
			[
				'default' => 'yes',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[standing][show_home_away]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Show Home/Away data', 'anwp-football-leagues-premium' ),
				'section'  => 'fl_standing',
				'settings' => 'anwp-fl-customizer[standing][show_home_away]',
				'choices'  => [
					'no'  => __( 'No', 'anwp-football-leagues' ),
					'yes' => __( 'Yes', 'anwp-football-leagues' ),
				],
			]
		);

		/*
		|--------------------------------------------------------------------
		| formation_show_player
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[match][formation_show_player]',
			[
				'default' => 'photo_shirt',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[match][formation_show_player]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Formation - Player', 'anwp-football-leagues-premium' ),
				'section'  => 'fl_match',
				'settings' => 'anwp-fl-customizer[match][formation_show_player]',
				'choices'  => [
					'photo_shirt' => __( 'Photo & Shirt', 'anwp-football-leagues-premium' ),
					'photo'       => __( 'Photo only', 'anwp-football-leagues-premium' ),
					'shirt'       => __( 'Shirt only', 'anwp-football-leagues-premium' ),
				],
			]
		);

		/*
		|--------------------------------------------------------------------
		| formation_show_country
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[match][formation_show_country]',
			[
				'default' => 'hide',
				'type'    => 'option',
			]
		);
		$wp_customize->add_control(
			'anwp-fl-customizer[match][formation_show_country]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Formation - Player Nationality', 'anwp-football-leagues-premium' ),
				'section'  => 'fl_match',
				'settings' => 'anwp-fl-customizer[match][formation_show_country]',
				'choices'  => [
					'hide' => __( 'Hide', 'anwp-football-leagues' ),
					'show' => __( 'Show', 'anwp-football-leagues' ),
				],
			]
		);

		/*
		|--------------------------------------------------------------------
		| formation_show_events
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[match][formation_show_events]',
			[
				'default' => 'show',
				'type'    => 'option',
			]
		);
		$wp_customize->add_control(
			'anwp-fl-customizer[match][formation_show_events]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Formation - Events', 'anwp-football-leagues-premium' ),
				'section'  => 'fl_match',
				'settings' => 'anwp-fl-customizer[match][formation_show_events]',
				'choices'  => [
					'hide' => __( 'Hide', 'anwp-football-leagues' ),
					'show' => __( 'Show', 'anwp-football-leagues' ),
				],
			]
		);

		/*
		|--------------------------------------------------------------------
		| formation_show_rating
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[match][formation_show_rating]',
			[
				'default' => 'show',
				'type'    => 'option',
			]
		);
		$wp_customize->add_control(
			'anwp-fl-customizer[match][formation_show_rating]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Formation - Rating', 'anwp-football-leagues-premium' ),
				'section'  => 'fl_match',
				'settings' => 'anwp-fl-customizer[match][formation_show_rating]',
				'choices'  => [
					'hide' => __( 'Hide', 'anwp-football-leagues' ),
					'show' => __( 'Show', 'anwp-football-leagues' ),
				],
			]
		);

		/*
		|--------------------------------------------------------------------
		| rating_min_color
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[general][rating_min_color]',
			[
				'default' => 5,
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[general][rating_min_color]',
			[
				'type'               => 'number',
				'description_hidden' => false,
				'description'        => __( 'default', 'anwp-football-leagues' ) . ': 5',
				'label'              => __( 'Minimum Color Rating', 'anwp-football-leagues-premium' ),
				'section'            => 'fl_general',
				'settings'           => 'anwp-fl-customizer[general][rating_min_color]',
			]
		);

		/*
		|--------------------------------------------------------------------
		| rating_max_color
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[general][rating_max_color]',
			[
				'default' => 9,
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[general][rating_max_color]',
			[
				'type'               => 'number',
				'description_hidden' => false,
				'description'        => __( 'default', 'anwp-football-leagues' ) . ': 9',
				'label'              => __( 'Maximum Color Rating', 'anwp-football-leagues-premium' ),
				'section'            => 'fl_general',
				'settings'           => 'anwp-fl-customizer[general][rating_max_color]',
			]
		);

	}
}
