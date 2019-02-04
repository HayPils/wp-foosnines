<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/HayPils
 * @since      1.0.0
 *
 * @package    Wp_Foosnines
 * @subpackage Wp_Foosnines/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wp_Foosnines
 * @subpackage Wp_Foosnines/public
 * @author     Hayden Pilsner <hpilsner@5nines.com>
 */
class Wp_Foosnines_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_Foosnines_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_Foosnines_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-foosnines-public.css', array(), $this->version, 'all' );
                wp_enqueue_style( 'bootstrap_css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css', array(), $this->version, 'all');
                
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_Foosnines_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_Foosnines_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-foosnines-public.js', array( 'jquery', 'google-charts' ), $this->version, true );
                wp_enqueue_script( 'bootstrap_js', "https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js", array('jquery'), $this->version, true);
                wp_enqueue_script( 'google-charts', "https://www.gstatic.com/charts/loader.js", array(), '', true);

	}

    // -------------------- ACTION CALLBACKS ----------------------

    public function deactivate_foos() {
        flush_rewrite_rules(TRUE);
    }

    public function create_match_post_type() {
        $labels = array(
            'name'               => __( 'Singles Matches' ),
            'singular_name'      => __( 'Singles Match' ),
        );

        $args = array(
            'labels'             => array(
                'name'               => _x( 'Singles Matches', 'post type general name' ),
                'singular_name'      => _x( 'Singles Match', 'post type singular name' ),
                'menu_name'          => _x( 'Singles Matches', 'admin menu' ),
                'name_admin_bar'     => _x( 'Singles Match', 'add new on admin bar' ),
                'add_new'            => _x( 'Add New', 'singles match' ),
                'add_new_item'       => __( 'Add New Singles Match' ),
                'new_item'           => __( 'New Singles Match' ),
                'edit_item'          => __( 'Edit Singles Match' ),
                'view_item'          => __( 'View Singles Match' ),
                'all_items'          => __( 'All Singles Matches' ),
                'search_items'       => __( 'Search Singles Matches' ),
                'parent_item_colon'  => __( 'Parent Singles Matches:' ),
                'not_found'          => __( 'No singles matches found.' ),
                'not_found_in_trash' => __( 'No singles matches found in Trash.' )
            ),
            'public'             => true,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array( 'title' ),
        );
        register_post_type( 'singles_match', $args);
    }

    public function tml_user_register_names( $user_id ) {
        if ( isset( $_POST['first_name'] ) && isset( $_POST['last_name'] ) ) {
            $first_name = filter_var( trim( $_POST['first_name'] ), FILTER_SANITIZE_STRING );
            $last_name = filter_var( trim( $_POST['last_name'] ), FILTER_SANITIZE_STRING );
            wp_update_user( array(
                    'ID'            => $user_id,
                    'first_name'    => $first_name,
                    'last_name'     => $last_name
                )
            );
        }
    }
    
    function redirect_to_profile() {
        $who = strtolower(sanitize_user($_POST['log']));
        $redirect_to = get_option('home');
        return $redirect_to;
    }
    
    function ajax_get_elo_history() {
        $player_id = intval($_REQUEST['player_id']);
        $elo_cont = new Foos_Elo_Controller();
        echo json_encode($elo_cont->get_elo_history($player_id));
        wp_die();
    }

}

