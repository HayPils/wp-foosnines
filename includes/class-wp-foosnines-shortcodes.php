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
        
    // -------------------- SHORTCODE CALLBACKS ----------------------

    /**
     * Generate the leader board html from the shortcut foos-leader-board
     * for the public-facing side of the site.
     *
     * @tag foosleaderboard
     * @since 1.0.0
     */
	public function foos_gen_leader_board( $atts ) {
	    $curr_blog_id = get_current_blog_id();
	    // players to display in rows on leader board in ranked order
        $all_players = get_users( 'blog_id='.$curr_blog_id.'&orderby=nicename' );
        if (isset($atts['top'])) {
            $num_of_players = intval($atts['top']);
        } else {
            $num_of_players = count($all_players);
        }

        // insertion sort all players in ranked order
        for ($i = 1; $i < count($all_players); $i++) {
            $index_shadow = $i;
            while ( $index_shadow > 0 && $this->rating($all_players[$index_shadow - 1]) < $this->rating($all_players[$index_shadow]) ) {
                $temp = $all_players[$index_shadow - 1]; // update previous player
                $all_players[$index_shadow - 1] = $all_players[$index_shadow]; // swap lower ranked player back
                $all_players[$index_shadow] = $temp;    // swap higher ranked player ahead
                $index_shadow--;
            }
        }

        $toRet = "<table>";    // HTML string generated for shortcode

        // header row
        $toRet .= "<tr>
                    <td>Rank</td>
                    <td></td>
                    <td></td>
                    <td>Wins</td>
                    <td>Losses</td>
                    <td>W/L Ratio</td>
                  </tr>";

        $rank_counter = 1;
        // fill table with all players
        for ($i = 0; $i < $num_of_players; $i++ ) {
                $player = $all_players[$i];
                $player_wins = get_user_meta($player->ID, 'wins', TRUE);
                $player_losses = get_user_meta($player->ID, 'losses', TRUE);
                $wl_ratio = round(($player_losses == 0) ? $player_wins : (float)$player_wins / (float)$player_losses, 2);
            if ($player_wins + $player_losses != 0 &&
                strcmp(trim($player->first_name), "") != 0 && strcmp(trim($player->last_name), "") != 0) {
                $toRet .= "<tr>
                        <td>" . $rank_counter . "</td>
                        <td><img src=\"" . get_avatar_url($player->ID, array('size' => 48)) . "\" style=\"padding-top: 5px\"></td>
                        <td>" . get_user_meta($player->ID, 'first_name', TRUE) . " "
                              . get_user_meta($player->ID, 'last_name', TRUE) . "</td>
                        <td>" . $player_wins . "</td>
                        <td>" . $player_losses . "</td>
                        <td>" . $wl_ratio . "</td>
                       </tr>";
                $rank_counter++;
            }
        }
        return $toRet .= "</table>";
    }

    public function foos_search_for_player() {
        if (!isset($_GET['player'])) {
            return '';
        }
        // sanitize search input
        $search_strings = explode(' ', esc_attr(trim($_GET['player'])));
        $wp_query = '';
        if (count($search_strings) === 1  && $search_strings[0] !== "") {
            $wp_query = array(
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key'     => 'first_name',
                        'value'   => $search_strings[0],
                        'compare' => '='
                    ),
                    array(
                        'key'     => 'last_name',
                        'value'   => $search_strings[0],
                        'compare' => '='
                    )
                )
            );
        } else if (count($search_strings) > 1){
            $wp_query = array(
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key'     => 'first_name',
                        'value'   => $search_strings[0],
                        'compare' => '='
                    ),
                    array(
                        'key'     => 'last_name',
                        'value'   => $search_strings[0],
                        'compare' => '='
                    ),
                    array(
                        'key'     => 'first_name',
                        'value'   => $search_strings[1],
                        'compare' => '='
                    ),
                    array(
                        'key'     => 'last_name',
                        'value'   => $search_strings[1],
                        'compare' => '='
                    ),
                )
            );
        }
        $players = new WP_User_Query($wp_query);
        $matched_players = $players->get_results();
        if (count($matched_players) == 0) { // no players received from query
            return '<p><b>Player ' . esc_html($_GET['player']) . ' not found, please try again.</b></p>';
        }

        $style = '<style>
                    .hover-tr {
                        background-color: white;
                        transition: background-color 0.25s;
                        -webkit-transition: background-color 0.25s;
                    }
                    
                    .hover-tr:hover {
                        background-color: #eee;
                        cursor:pointer;
                        transition: background-color 0.25s;
                        -webkit-transition: background-color 0.25s;
                    }
                  </style>';

        $doc = '<h1 style="padding-top: 55px;">Select a player</h1>
                <table style="margin-top: 10px;">
                       <tr>
                        <td>Name</td>
                        <td>Nickname</td>
                        <td>Email</td>
                        <td>Wins</td>
                        <td>Losses</td>
                       </tr>';

        $player_counter = 0;
        $player_js_array = '';
        foreach ($matched_players as $matched_player) {
            // populate doc search table
            if ($matched_player->ID !== get_current_user_id()) {
                $doc .= '<tr id="player_' . $player_counter . '" data-user-id="' . $matched_player->ID . '" class="hover-tr">'
                    . '<td>' . $matched_player->first_name . ' ' . $matched_player->last_name . '</td>'
                    . '<td>' . $matched_player->nickname . '</td>'
                    . '<td>' . $matched_player->user_email . '</td>'
                    . '<td>' . $matched_player->wins . '</td>'
                    . '<td>' . $matched_player->losses . '</td>'
                    . '</tr>';

                // populate js array string
                $player_js_array .= '["'
                    . $matched_player->first_name . ' ' . $matched_player->last_name . '", "'
                    . $matched_player->wins . '", "'
                    . $matched_player->losses . '", "'
                    . get_avatar_url($matched_player->ID, 30)
                    . '"]';
                if ($player_counter < count($matched_players) - 1) {
                    $player_js_array .= ', ';
                }
                $player_counter++;
            }
        }
        if ($player_counter === 0) {    // don't build table if no users can be listed
            return '';
        }

        // get current user data
        $current_user = wp_get_current_user();
        $current_user_meta = get_user_meta(get_current_user_id());

        $script = '<script>
                    var matched_players = [' . $player_js_array . '];
                    
                    //set player 1 attributes
                    jQuery("#player_1_avatar").attr("src", "' . get_avatar_url(get_current_user_id(), 30) . '");
                    jQuery("#player_1_name").html("<b>' . $current_user->first_name . ' ' . $current_user->last_name . '</b>");
                    jQuery("#p_1_wins").text("Wins: ' . $current_user_meta["wins"][0] . '");
                    jQuery("#p_1_losses").text("Losses: ' . $current_user_meta["losses"][0] . '");
                    
                    // Get the player button elements and set visibility functions for modals
                    for(var i = 0; i < ' . $player_counter . '; i++) {
                        var dummy = i;
                        jQuery("#player_" + i).click(function() {
                            // set player 2 attributes
                            jQuery("#player_2_avatar").attr("src", matched_players[dummy][3]);
                            jQuery("#player_2_name").html("<b>" + matched_players[dummy][0] + "</b>");
                            jQuery("#p_2_wins").text("Wins: " + matched_players[dummy][1]);
                            jQuery("#p_2_losses").text("Losses: " + matched_players[dummy][2]);
                            
                            // set player data-opp-id to respective data-user-id
                            jQuery("#p2ID").attr("value", jQuery("#player_" + dummy).attr("data-user-id"));
                            
                            // show modal
                            jQuery("#startMatchModal").modal();
                        });
                    }
                   </script>';

        $doc .= '</table>' . do_shortcode('[foos-startmatchmodal]');  // generate start match modal

        return $style . $doc . $script;
    }

    public function foos_start_match_modal() {
        $doc = '<div id=startMatchModal class="modal fade" tabindex="-1" style="top: 15%" role="dialog">
                  <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                      <div class="modal-header" align="center">
                        <h3 class="modal-title" style="margin: auto;">Singles Exhibition Match</h3>
                      </div>
                      <div class="modal-body">
                        <div class="container">
                            <div class="row">
                                <div class="col" style="text-align:center">
                                    <img id="player_1_avatar" src="" alt="Your profile avatar" style="margin-left: auto; margin-right: auto; display: block">
                                    <h5 id="player_1_name" style="padding-top: 20px;"></h5>
                                    <div class="row" style="margin: auto;">
                                        <div id="p_1_wins" class="col"></div>
                                        <div id="p_1_losses" class="col"></div>
                                    </div>
                                </div>
                                <div class="col-" style="padding-top: 10%"><h3><b>VS.</b></h3></div>
                                <div class="col" style="text-align:center">
                                    <img id="player_2_avatar" src="" alt="Opponent profile avatar" style="margin-left: auto; margin-right: auto; display: block">
                                    <h5 id="player_2_name" style="padding-top: 20px;"></h5>
                                    <div class="row" style="margin: auto;">
                                        <div id="p_2_wins" class="col"></div>
                                        <div id="p_2_losses" class="col"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <form action="http://foos.5nines.com/matches/" method="post">
                            <input type="hidden" id="p1ID" name="p1id" value="' . get_current_user_id() . '">
                            <input type="hidden" id="p2ID" name="p2id" value="">
                            <button type="submit" id="startBtn" class="btn btn-primary">Start Match</button>
                        </form>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                      </div>
                    </div>
                  </div>
                </div>';

	    $script = '<script>
                    // move modal outside of divs to end of body for proper z-indexing
                    jQuery(document).ready(function() {
                        jQuery("#startMatchModal").appendTo(document.body);
                    });
                   </script>';

	    return $doc . $script;
    }


    public function foos_player_info($atts) {
        $toRet = "";
        // Display current user's stats when player attribute is set to self
        if (isset($atts['player']) && strcmp($atts['player'], 'self') == 0) {
            $this_user = wp_get_current_user();
            $toRet .= '<h1><b>' . $this_user->display_name . '</b></h1>';
        } else {
            if (!isset($_GET['player'])) {
                $opponent_name = filter_input(INPUT_GET, "player", FILTER_SANITIZE_STRING);
            }
            $toRet .= '<h1><b>' . $toRet . '</b></h1>';
        }
        return $toRet;
    }
    
    /**
     * Returns rank of a player based on W/L ratio multiplied by
     * number of played games
     *
     * @param $user   player to rank
     * @return bool|float|int   FALSE if player stats are not integers, else
     *                          returns player rank as float or int
     */
    private function rating( $user ) {
        $options = array(
            'options' => array(
                'default' => -1, // value to return if the filter fails
                // other options here
                'min_range' => -1
            )
        );
        $wins = filter_var( get_user_meta( $user->ID, 'wins', TRUE ), FILTER_VALIDATE_INT, $options );
        $losses = filter_var( get_user_meta( $user->ID, 'losses', TRUE ), FILTER_VALIDATE_INT, $options );
        if ( $wins == -1 || $losses == -1 ) {
            return FALSE;
        }
        if ( $losses == 0 ) {
            return (float)($wins + 1) * $wins;
        }
        return ((float)$wins / (float)$losses) * ($wins + $losses);
    }
}

