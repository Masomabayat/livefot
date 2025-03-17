=== AnWP Football Leagues Premium ===
Contributors: anwppro, freemius
Tags:              football, soccer, sport, football league, football management, league management, club management, football club
Requires at least: 5.3
Requires PHP:      7.0
Tested up to:      6.4
Stable tag:        0.16.1
License:           GPLv2 or later
License URI:       http://www.gnu.org/licenses/gpl-2.0.html

Premium Add-on for Football Leagues plugin.

== Changelog ==

= 0.16.1 - 2024-02-08 =
* fixed: Transfers shortcode layout="player" - custom club_out is not rendering
* fixed: problem with rendering temp players in the game lineups

= 0.16.0 - 2024-02-07 =
* introduced a new database structure. To complete the update, a migration process will be necessary.
* changed: increased the minimal version to PHP 7.0
* improved: performance improvements
* fixed: "date_from" and "date_to" arguments in the Player Stats Panel shortcode are not working properly
* improved: API Import settings have been moved to a dedicated page
* fixed: Subteams summary page is not working properly
* improved: automatic user's timezone functionality (logic of kickoff time converting)
* improved: Calendar Slider performance

= 0.15.3 - 2023-07-30 =
* added: new Standing Table Advanced shortcode
* fixed: mobile YouTube loading error
* fixed: LIVE manual scores 0:0 on start
* fixed: minor API Import fixes

= 0.15.2 - 2023-07-05 =
* improved: minor CSS fixes
* improved: updated some dependent libraries

= 0.15.1 - 2023-07-01 =
* added: new arguments in "Stats :: Players (Custom)" shortcode: "filter by columns" and "filter by column value (min)"
* added: new layout in "Stats :: Players (Custom)" shortcode: "tabulator" with sorting and filtering
* improved: %kickoff% placeholder in Builder will use plugin date format (if set in Settings >> Display >> Custom Match Date format)
* added: icons to basic statistics in "Stats :: Clubs" shortcode

= 0.15.0 - 2023-06-13 =
* improved: public JavaScript files have been rewritten in modern JS (removed jQuery dependency, make future maintenance easier)
* replaced: DataTables library with Tabulator (all dependent shortcodes have been rewritten)
* added: new animated countdown (instead of the old flipping)
* added: new Block MegaMenu add-on (create simple mega-menu in almost any theme)
* added: new Custom Sidebars add-on
* deprecated: Aneto theme is deprecated and no longer maintained (but you can use it as long as you want). Our theme recommendations are Kadence, GeneratePress, Blocksy, or any other classic theme.
* added: LIVE Matches shortcode
* fixed: MatchWeeks Slides (shortcode) - matchweek argument is not working in a hidden tab

= 0.14.18.3 - 2023-04-03 =
* fixed: rendering Calendar Slider and widget with non-Latin characters in no-data-text after installing v0.14.18.2

= 0.14.18.2 - 2023-03-30 =
* improved: performance improvements
* fixed: bracket manual layout has incorrect order in some cases

= 0.14.18.1 - 2023-03-23 =
* improved: manual LIVE - performance and cache improvements

= 0.14.18 - 2023-03-10 =
* added: Standing - home/away table data
* added: Game Formation - new display options (photo & shirt, rating, game events, nationality) - "Customizer" >> "Football Leagues" >> "Match"
* added: player rating color depends on the value (from red to blue). You can change min/max values in "Customizer" >> "Football Leagues" >> "General"
* fixed: Standing table colors with Conference support

= 0.14.17 - 2023-02-06 =
* fixed: Full Page Caching - improved W3 Total Cache support
* fixed: Frontend Edit - game events on save don't update the plugin cache
* fixed: Layout Builder - Stadium season selector not visible
* improved: Import API - minor UI fixes

= 0.14.16 - 2023-01-09 =
* improved: API Import - LIVE update logic
* added: Experimental support for the full-page caching (purge cache on API Import - game update)

= 0.14.15 - 2022-12-12 =
* improved: LIVE Import - LIVE scores data are updated by WP Rest API that can be cached on web server level

= 0.14.14 - 2022-12-03 =
* fixed: API Import - Penalty Shootout parsing

= 0.14.13 - 2022-11-29 =
* fixed: Import API - minor LIVE import improvements
* improved: Bracket - added "free mode" and kickoff time field

= 0.14.12 - 2022-11-19 =
* improved: API Import - minor fixes on LIVE import
* added: API Import - added option to force update player photos on squad update
* added: VAR event

= 0.14.11 - 2022-11-06 =
* fixed: Player: "all seasons" as the default player page doesn't work in some situations
* fixed: Standings shortcode: incorrect sorting by title
* fixed: manual LIVE is not working together with API LIVE Import
* fixed: logic in calculating Standing Table arrows

= 0.14.10.1 - 2022-10-22 =
* fixed: API Import - error with 00:00:00 GMT time
* fixed: LIVE block style in slim game layout with competition logo

= 0.14.10 - 2022-10-19 =
* added: automatic user timezone functionality + timezone switcher ( #1530 )
* added: Layout Builder > Player - "Show on Player Position" filter option
* added: new arguments in Calendar Slider shortcode (calendar_size, competition_title, centered, day_width )
* added: new argument in Referees Stats shortcode - order
* improved: API Import - LIVE Import: improved performance

= 0.14.9.1 - 2022-09-15 =
* fixed: display values in Player Stats Panel without rating field

= 0.14.9 - 2022-09-15 =
* added: Dynamic SEO - conditional tags
* fixed: empty row in Referees Stats shortcode (games without referee assigned)
* fixed: kickoff game edit in frontend Game edit mode
* added: new dynamic variables in Layout Builder - Match: referee_id and referee_name
* added: new arguments "date_from" and "date_to" in Shortcodes: Stat Players, Stats Players (Custom) & Players Stats
* added: new Player Stats Panel shortcode (improved version of old Player's Stats Panel)

= 0.14.8 - 2022-09-02 =
* improved: loading some scripts by request only (DataTables, Flatpickr)
* improved: added optimized Swiper library (twice smaller). If you have a problem with it, disable it in FL+ Customizer
* added: API Import - update stadiums and referees on "Update Kickoff time" action
* added: API Import - option to reset squad on update (NO - default)
* added: new "plain" layout in shortcode Matches
* added: new Referee Stats shortcode
* added: new Referees Stats shortcode

= 0.14.7.1 - 2022-07-30 =
* fixed: rendering SEO title of non-plugin pages in SEO plugins

= 0.14.7 - 2022-07-30 =
* fixed: Transfers shortcode - show team title with a logo
* added: dynamic SEO title and descriptions in Layout Builder
* added: more dynamic variables in Layout Builder
* fixed: API Import: no games to create in some situations (when API League's "from" parameter is not set correctly)

= 0.14.6 - 2022-06-28 =
* added: API Import - support Cancelled game status on import
* added: API Import - update Team code (abbreviation)
* fixed: API Import - error on creating games on some servers
* added: API Import - (Beta) Automatic Import Wizard for the most popular leagues

= 0.14.5 - 2022-05-30 =
* removed: API Import V2. If you are still using V2, migrate to V3 before updating.
* added: API Import - Import Coaches
* added: Standing - new Ranking rules: "less yellow cards" and "less red cards"
* fixed: API Import - update game title and slug after Kickoff update

= 0.14.4.1 - 2022-04-20 =
* fixed: Standing table incorrect rendering on some sites

= 0.14.4 - 2022-04-20 =
* added: Layout Builder - Prediction block in Match Live
* added: API Import - Odds - option to hide selected bookmakers and odds clickable option
* added: possibility to translate LIVE statuses
* added: API Import - translatable Prediction parts
* added: Stats :: Players (Custom) Shortcode - arguments for secondary sorting - sort_column_2 and sort_order_2
* added: ▼ ▲ in the Standing Table

= 0.14.3 - 2022-04-03 =
* added: API Import V3 - Odds Import
* added: new variable %ref_4_name% in Send Game Report by email

= 0.14.2 - 2022-03-24 =
* added: Live Scores support in Matches widget
* minor improvements and fixes

= 0.14.1 - 2022-03-17 =
* added: game Club captain
* added: Game Lineups - minutes in events (Customizer >> FL >> Match >> Minutes in Lineups Events)
* fixed: automatic suspension error in the last game
* added: API Import - options to disable loading club logos and stadium photos

= 0.14.0.1 - 2022-03-16 =
* fixed: block header styles in Layout Builder (global shortcode and competition)
* fixed: duplicate player season dropdown in Layout Builder

= 0.14.0 - 2022-03-16 =
* improved: Transfers shortcode - mobile layouts
* fixed: API Import - Update Injuries - disable scheduled task error
* added: API Import - option to disable loading players photos

= 0.13.8 - 2022-02-28 =
* security fix

= 0.13.7 - 2022-01-06 =
* added: API Import (V3) - Import Injuries (data are available in top leagues only)
* added: new Card Suspension functionality
* added: new shortcode - "Suspension Risk" (based on Card Suspension)
* added: possibility to Automatically Suspend Players (based on Card Suspension)
* improved: performance (Swiper and ECharts scripts are loading only on the pages where needed)
* added: Layout Builder - %home_goals% and %away_goals% dynamic variable in Match

= 0.13.6 - 2021-12-16 =
* fixed: API Import V3: problem with importing games in new rounds (in some national Cups)

= 0.13.5 - 2021-12-14 =
* minor improvements

= 0.13.4 - 2021-12-10 =
* added: Plugin Caching System - for caching some complex queries and automatic cache invalidating
* improved: API Import (V3) - dashboard scheduled UI
* added: API Import (V3) - option to schedule Update Kickoff Time task (once daily)
* added: API Import (V3) - option to schedule Update Predictions task (once daily)
* added: API Import - option to change (or translate) prediction advice in the Game Edit page
* added: API Import (V3) - option to load team comparison prediction data ( + block in Layout Builder )
* added: API Import (V3) - option to schedule Update Lineups & Formations task before the game (40 min before)

= 0.13.3 - 2021-10-22 =
* fixed: API Import (V3): import half-time score
* added: API Import (V3): list of latest log records in Dashboard and "All Leagues" Competition modal
* improved: Charts shortcode: added "league_id" and "limit" last games arguments

= 0.13.2 - 2021-10-18 =
* added: Plugin Health page to check some common problems
* improved: minor API Import V3 improvements
* added: support for several competition IDs in "Matches Horizontal Scoreboard" shortcode

= 0.13.1 - 2021-10-08 =
* fixed: API Import (V3): Standings are not calculated automatically in secondary stages of multistage competitions
* fixed: API Import (V3): dropdown selector in Manual Configuration is not visible on non-English sites
* added: API Import (V3): option to disable SSL certificate verification
* improved: Transfers block is hidden on the National Club page

= 0.13.0.1 - 2021-10-05 =
* improved: API Import Migrator not worked with some new competitions and RapidAPI

= 0.13.0 - 2021-10-04 =
* added: API Import (v3): new API Import UI which works with api-football.com API V3
* added: API Import (v3): new Manual Import Configuration creator
* improved: API Import (v3): improved multistage competition management
* improved: API Import (v3): improved transfers import logic to prevent duplicates
* added: new argument "competition_country" in Calendar Slider shortcode
* improved: Layout Builder: rendering "Matches All" in Multistage Competitions
* improved: Missed Players and Cards shortcode: new argument "show_date", new option "competition" in "club_id", fixed "all_players" when sections is set to "missed"

= 0.12.8.1 - 2021-08-01 =
* fixed: save match Stadium

= 0.12.8 - 2021-08-01 =
* improved: Game Formation edit UI
* fixed: Formation order in RTL mode and in Home team in vertical layout
* added: Formation - Custom Color option in Game
* fixed: Send Game Report by email - placeholder %away_cards_yr%
* added: Send Game Report by email - new placeholders

= 0.12.7.1 - 2021-07-04 =
* fixed: error on editing Club Squad and Trophies in some cases

= 0.12.7 - 2021-07-02 =
* added: Layout Builder - additional dynamic variables in Match layout (club, competition, leagues, season names)
* fixed: rendering custom game team stats in PHP 5.6
* added: API Import - Lineup Formations
* added: possibility to send game reports by email
* fixed: minor fixes

= 0.12.6 - 2021-06-19 =
* added: option to create new player transfer in Squad on Club edit page
* improved: club trophies UI
* added: clone action in Layout Builder
* added: More filters in the Transfers admin list
* added: new option "Transfer End Date" in Transfers
* added: API Import - possibility to translate "free" and "loan" in Transfers (Text Options). Will work on new or updated transfers.

= 0.12.5 - 2021-06-07 =
* fixed: transfer status not saved properly

= 0.12.4 - 2021-05-21 =
* added: new shortcode "Club Stats" (Single Club Statistics)
* added: new block "Club Stats" in Layout Builder > Club (based on shortcode "Club Stats")
* improved: Statistics Configurator UI
* added: new shortcode "Stats :: Clubs" (Interactive table with clubs and specified statistics)
* added: API Import - Additional Club Statistics

= 0.12.3.1 - 2021-04-28 =
* fixed: saving season in the new Transfer UI

= 0.12.3 - 2021-04-28 =
* fixed: shortcode "Stat Players (Single Stat Value)" - number format in decimal values
* improved: Transfer edit page UI
* added: Transfer edit page - new actions to "Set as Current club" and "Register in Squad"
* added: Layout Builder - %kickoff% dynamic variable in Match and Match Live

= 0.12.2 - 2021-04-07 =
* added: new "Head to Head Team Stats" shortcode
* added: new "Stats :: Players (Custom)" shortcode
* added: API Import - option to specify national teams: in "Assign Teams To Groups" (for new clubs) and in "Create or Update Teams" (for existing clubs)
* improved: API Import - "Assign Teams to Groups" tool: new UI, no more need to create groups, handle complex competitions

= 0.12.1 - 2021-03-21 =
* fixed: loading media library script in some cases (error on Competition edit page)

= 0.12.0 - 2021-03-21 =
* added: shortcode and widget "Stat Players (Single Stat Value)"
* improved: API Import - "Update Players" action from the Dashboard uses the main RapidAPI subscription (not BETA)
