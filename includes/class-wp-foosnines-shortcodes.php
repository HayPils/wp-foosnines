<?php

/**
 * The shortcode functionality of the plugin.
 *
 * @link       https://github.com/HayPils
 * @since      1.0.0
 *
 * @package    Wp_Foosnines
 * @subpackage Wp_Foosnines/includes
 */

/**
 * The shortcode functionality of the plugin.
 *
 * Defines the plugin name, version, and shortcode callbacks for
 * the plugin.
 *
 * @package    Wp_Foosnines
 * @subpackage Wp_Foosnines/includes
 * @author     Hayden Pilsner <hpilsner@5nines.com>
 */
class Wp_Foosnines_Shortcodes {

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
        
    public function leaderboard( $atts ) {
        $leaderboard = new Foos_Leaderboard();
        $num_of_players = isset($atts['top']) ? intval($atts['top']) : -1;
        ob_start();
        $leaderboard->print_leaderboard($num_of_players);
        $leaderboard->enqueue_js();
        return ob_get_clean();
    }
    
    public function top_stat_board() {
        $top_stat_board = new Foos_Top_Stats_Board();
        ob_start();
        $top_stat_board->print_board();
        $top_stat_board->enqueue_js();
        return ob_get_clean();
    }
    
    public function match_board() {
        $match_board = new Foos_Match_Board();
        ob_start();
        $match_board->singles_list();
        return ob_get_clean();
    }
    
    public function my_matches() {
        $match_cont = new Foos_Match_Controller();
        
        // attempt to create new match if player ids in request vars
        if (isset($_POST['p1id']) && isset($_POST['p2id'])) {
            $p1_id = intval($_POST['p1id']);
            $p2_id = intval($_POST['p2id']);
            $match_cont->create_singles_match($p1_id, $p2_id);
        }
        
        // attempt to submit a match score
        $valid_submit = true;
        if (isset($_POST['match_id']) && isset($_POST['p1_score']) && isset($_POST['p2_score'])) {
            $submit_match_id = intval($_POST['match_id']);
            $p1_score = intval($_POST['p1_score']);
            $p2_score = intval($_POST['p2_score']);
            $valid_submit = $match_cont->submit_score($submit_match_id, $p1_score, $p2_score);
        }
        $my_matches = new Foos_My_Matches(get_current_user_id(), $valid_submit, $submit_match_id);
        $my_matches->enqueue_js();
        
        ob_start();
        $my_matches->list_menu();
        $my_matches->inp_singles_match_list();  // print in progress singles match list
        $my_matches->final_singles_match_list();    // print final singles match list
        return ob_get_clean();
    }
    
    // player info page shortcode
    public function player_info() {
        $player_info = new Foos_Player_Info();
        ob_start();
        $player_info->stats_portfolio(intval($_REQUEST['player_id']));
        $player_info->enqueue_js();
        return ob_get_clean();
    }
    
 }
