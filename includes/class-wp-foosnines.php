<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/HayPils
 * @since      1.0.0
 *
 * @package    Wp_Foosnines
 * @subpackage Wp_Foosnines/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wp_Foosnines
 * @subpackage Wp_Foosnines/includes
 * @author     Hayden Pilsner <hpilsner@5nines.com>
 */
class Wp_Foosnines {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wp_Foosnines_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'PLUGIN_NAME_VERSION' ) ) {
			$this->version = PLUGIN_NAME_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'wp-foosnines';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
                if (!is_admin())
                    $this->define_shortcode_callbacks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wp_Foosnines_Loader. Orchestrates the hooks of the plugin.
	 * - Wp_Foosnines_i18n. Defines internationalization functionality.
	 * - Wp_Foosnines_Admin. Defines all hooks for the admin area.
	 * - Wp_Foosnines_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-foosnines-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-foosnines-i18n.php';
                
                /**
		 * The class is responsible for defining the shortcode functionality of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-foosnines-shortcodes.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-foosnines-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-foosnines-public.php';

		$this->loader = new Wp_Foosnines_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wp_Foosnines_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Wp_Foosnines_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Wp_Foosnines_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Wp_Foosnines_Public( $this->get_plugin_name(), $this->get_version() );

                /* ACTION HOOK REGISTRATION */
                // load public facing styles and scripts
                $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
                $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

                // Register creation of match custom post type
                $this->loader->add_action( 'init', $plugin_public, 'create_match_post_type' );
                $this->loader->add_action(  'shutdown', $plugin_public, 'deactivate_foos');

                $this->loader->add_action( 'user_register', $plugin_public, 'tml_user_register_names' );
                
                $this->loader->add_filter( 'login_redirect', $plugin_public, 'redirect_to_profile' );

	}
        
        private function define_shortcode_callbacks() {
            
                $plugin_shortcodes = new Wp_Foosnines_Shortcodes( $this->get_plugin_name(), $this->get_version() );
                        
                $this->loader->add_shortcode( 'foos_leaderboard', $plugin_shortcodes, 'foos_gen_leader_board' );
                $this->loader->add_shortcode( 'foos-top-stat-board', $plugin_shortcodes, 'top_stat_board' );
                $this->loader->add_shortcode( 'foos-searchforplayer', $plugin_shortcodes, 'foos_search_for_player' );
                $this->loader->add_shortcode( 'foos-playerinfo', $plugin_shortcodes, 'foos_player_info' );
                $this->loader->add_shortcode( 'foos-startmatchmodal', $plugin_shortcodes, 'foos_start_match_modal' );
                $this->loader->add_shortcode( 'foos-match-board', $plugin_shortcodes, 'match_board' );
                $this->loader->add_shortcode( 'foos-my-matches', $plugin_shortcodes, 'my_matches' );
                
        }

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Wp_Foosnines_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
