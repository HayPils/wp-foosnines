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
                    <td>Rating</td>
                  </tr>";

        $rank_counter = 1;
        // fill table with all players
        for ($i = 0; $i < $num_of_players; $i++ ) {
            $player = $all_players[$i];
            $player_wins = intval(get_user_meta($player->ID, 'foos_wins', TRUE));
            $player_losses = intval(get_user_meta($player->ID, 'foos_losses', TRUE));
            $wl_ratio = round(($player_losses == 0) ? $player_wins : (float)$player_wins / (float)$player_losses, 2);
            if ($player_wins + $player_losses != 0) {
                $toRet .= "<tr>
                        <td>" . $rank_counter . "</td>
                        <td style='padding-top:12px;'>" . get_avatar($player->ID, 60) . "</td>
                        <td>" . $this->foos_name($player) . "</td>
                        <td>" . $player_wins . "</td>
                        <td>" . $player_losses . "</td>
                        <td>" . $wl_ratio . "</td>
                        <td>" . round($this->rating($player), 2) . "</td>
                       </tr>";
                $rank_counter++;
            }
        }
        return $toRet .= "</table>";
    }
    
    public function top_stat_board() {
        ob_start();
        $all_players = get_users( 'blog_id='.$curr_blog_id.'&orderby=nicename' );
        $max_streak = 0;
        $max_lws = 0;
        $top_steakers = [];
        $top_lws = [];
        
        foreach ($all_players as $player) {
            $player_streak = get_user_meta($player->ID, 'foos_ws', true);   // get player win streak
            $player_lws = get_user_meta($player->ID, 'foos_lws', true);   // get player longest win streak

            // process longest win streak data
            if ($player_lws > $max_lws) {
                $top_lws = [$player];
                $max_lws = $player_lws;
            } else if ($player_lws == $max_lws) {
                array_push($top_lws, $player);
            }

            // process on fire data
            if ($player_streak > $max_streak) {
                $top_streakers = [$player];
                $max_streak = $player_streak;
            } else if ($player_streak == $max_streak){
                array_push($top_streakers, $player);
            }
            
        }
        
        ?>
<div class="contianer-flex">
    <!-- Record win streak -->
    <div class="row">
        <div class="col-sm-4">
            <h2>Record Win Streak: </h2>
        </div>
        <div class="col">
            <h3 style="margin-top:3px;">
            <?php
                for ($i = 0; $i < count($top_lws); $i++) {
                    echo $top_lws[$i]->display_name;
                    echo ($i < count($top_lws) - 1) ? ', ' : ' ';
                }
            ?>
            </h3>
        </div>
        <div class="col-sm-2">
            <h3 style="margin-top:3px;"><?php echo ''.$max_lws.' wins'; ?></h3>
        </div>
    </div>
    
    <!-- Longest win streak (On fire >= 3 wins) -->
    <div class="row">        
        <div class="col-sm-4">
            <h2>On Fire 🔥:</h2>
        </div>
        <div class="col">
            <h3 style="margin-top:3px;">
            <?php
                if ($max_streak > 2) {
                     for ($i = 0; $i < count($top_streakers); $i++) {
                         echo $top_streakers[$i]->display_name;
                         echo ($i < count($top_streakers) - 1) ? ', ' : ' ';
                     }
                } else {
                    echo 'No one is on fire 🥶';
                }
            ?>
            </h3>
        </div>
        <div class="col-sm-2">
            <h3 style="margin-top:3px;"><?php echo ($max_streak > 2) ? ''.$max_streak.' wins' : ''; ?></h3>
        </div>
    </div> 
</div>
        <?php
        return ob_get_clean();
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
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key'     => 'last_name',
                        'value'   => $search_strings[0],
                        'compare' => 'LIKE'
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
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key'     => 'last_name',
                        'value'   => $search_strings[0],
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key'     => 'first_name',
                        'value'   => $search_strings[1],
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key'     => 'last_name',
                        'value'   => $search_strings[1],
                        'compare' => 'LIKE'
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
                        <td>Display Name</td>
                        <td>Email</td>
                        <td>Wins</td>
                        <td>Losses</td>
                       </tr>';

        $player_counter = 0;
        $player_js_array = [];
        foreach ($matched_players as $matched_player) {
            // populate doc search table
            if ($matched_player->ID !== get_current_user_id()) {
                $doc .= '<tr id="player_' . $player_counter . '" data-user-id="' . $matched_player->ID . '" class="hover-tr">'
                    . '<td>' . $matched_player->first_name . ' ' . $matched_player->last_name . '</td>'
                    . '<td>' . $matched_player->display_name . '</td>'
                    . '<td>' . $matched_player->user_email . '</td>'
                    . '<td>' . $matched_player->foos_wins . '</td>'
                    . '<td>' . $matched_player->foos_losses . '</td>'
                    . '</tr>';

                // populate js array string
                array_push($player_js_array, [
                    'name' => $matched_player->first_name . " " . $matched_player->last_name,
                    'wins' => $matched_player->foos_wins,
                    'losses' =>$matched_player->foos_losses,
                    'avatar' => get_avatar($matched_player->ID, 80)
                ]);
                $player_counter++;
            }
        }
        if ($player_counter === 0) {    // don't build table if no users can be listed
            return '';
        }
        
        $script = '<script>
                    var matched_players = ' . json_encode($player_js_array) . ';
                    
                    // Get the player button elements and set visibility functions for modals
                    for(var i = 0; i < ' . $player_counter . '; i++) {
                        var dummy = i;
                        jQuery("#player_" + i).click(function() {
                            // set player 2 attributes
                            jQuery("#player_2_avatar").empty();
                            jQuery("#player_2_avatar").append(matched_players[dummy]["avatar"]);
                            jQuery("#player_2_name").html("<b>" + matched_players[dummy]["name"] + "</b>");
                            jQuery("#p_2_wins").text("Wins: " + matched_players[dummy]["wins"]);
                            jQuery("#p_2_losses").text("Losses: " + matched_players[dummy]["losses"]);
                            
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
        // get current user data
        $current_user = wp_get_current_user();
        $current_user_meta = get_user_meta(get_current_user_id());
        ob_start(); ?>
        <div id=startMatchModal class="modal fade" tabindex="-1" style="top: 15%" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
              <div class="modal-content">
                <div class="modal-header" align="center">
                  <h3 class="modal-title" style="margin: auto;">Singles Exhibition Match</h3>
                </div>
                <div class="modal-body">
                  <div class="container">
                      <div class="row">
                          <div class="col" style="text-align:center">
                              <?php echo get_avatar($current_user->ID, 80); ?>
                              <h5 id="player_1_name" style="padding-top: 20px;"><b><?php echo $current_user->first_name . ' ' . $current_user->last_name ?></b></h5>
                              <div class="row" style="margin: auto;">
                                    <div id="p_1_wins" class="col">Wins: <?php echo $current_user_meta["foos_wins"][0] ?></div>
                                    <div id="p_1_losses" class="col">Losses: <?php echo $current_user_meta["foos_losses"][0] ?></div>
                              </div>
                          </div>
                          <div class="col-" style="padding-top: 10%"><h3><b>VS.</b></h3></div>
                          <div class="col" style="text-align:center">
                              <div id="player_2_avatar"></div>
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
                  <form action="http://foos.5nines.com/my-matches/" method="post">
                      <input type="hidden" id="p1ID" name="p1id" value="<?php echo $current_user->ID ?>">
                      <input type="hidden" id="p2ID" name="p2id" value="">
                      <button type="submit" id="startBtn" class="btn btn-primary">Start Match</button>
                  </form>
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
              </div>
            </div>
          </div>

        <script>
            // move modal outside of divs to end of body for proper z-indexing
            jQuery(document).ready(function() {
                jQuery("#startMatchModal").appendTo(document.body);
            });
        </script>

        <?php
        return ob_get_clean();
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
    
    public function match_board() {
        // get all singles matches
        $singles_ids = new WP_Query([
            'post_type' => 'singles_match',
            'posts_per_page'    => -1,
            'meta_query'    => [
                [
                    'key'   => 'is_final',
                    'value' => '1',
                    'compare' => '='
                ]
            ],
            'fields'    => 'ids'
        ]);
        $singles_ids = $singles_ids->posts;
        ob_start(); ?>
<div class="container-flex" style="margin-bottom:50px;">
    <div class="row justify-content-md-center">
        <div class="col-sm-5 foos-menu-selector" style="background-color:lightgray;"><h1 style="margin-top:10px;">Singles</h1></div>
        <div class="col-sm-5 foos-menu-selector"><h1 style="margin-top:10px;">Doubles</h1></div>
    </div>
</div>

<!-- singles matches -->
<div class="container-flex">
        <?php
        foreach ($singles_ids as $match_id) :
            $p1_id = get_post_meta($match_id, 'p1_id', true);
            $p2_id = get_post_meta($match_id, 'p2_id', true);
            $p1_user = get_userdata($p1_id);
            $p2_user = get_userdata($p2_id);
            $p1_name = $p1_user->display_name;
            $p2_name = $p2_user->display_name;
            ?>
    <div class="row justify-content-md-center" style="margin-bottom:20px;">
        <div class="col-sm-3">
            <div class="row">
                <div class="col-"><?php echo get_avatar($p1_id, 70) ?></div>
                <div class="col"><h3><?php echo $p1_name ?></h3></div>
            </div>
        </div>
        <div class="col-sm-3" style="text-align:center;">
            <h3><?php echo get_post_meta($match_id, 'p1_score', true) ?> - <?php echo get_post_meta($match_id, 'p2_score', true) ?></h3>
        </div>
        <div class="col-sm-3" style="text-align:right;">
            <div class="row">
                <div class="col"><h3><?php echo $p2_name ?></h3></div>
                <div class="col-"><?php echo get_avatar($p2_id, 70) ?></div>
            </div>
        </div>
    </div>
            <?php
        endforeach; ?>
</div>
        <?php
        return ob_get_clean();
    }
    
    public function my_matches() {
        // attempt to create new match if player ids in request vars
        if (isset($_POST['p1id']) && isset($_POST['p2id'])) {
            $p1_id = intval($_POST['p1id']);
            $p2_id = intval($_POST['p2id']);
            $this->create_singles_match($p1_id, $p2_id);
        }
        
        // attempt to submit a match score
        $valid_submit = true;
        if (isset($_POST['match_id']) && isset($_POST['p1_score']) && isset($_POST['p2_score'])) {
            $submit_match_id = intval($_POST['match_id']);
            $p1_score = intval($_POST['p1_score']);
            $p2_score = intval($_POST['p2_score']);
            $valid_submit = $this->submit_score($submit_match_id, $p1_score, $p2_score);
        }
        
        $curr_user_id = get_current_user_id();
        $inp_singles = $this->get_user_inp_singles($curr_user_id);
        $final_singles = $this->get_user_final_singles($curr_user_id);
        
        ob_start(); ?>
<div class="container-flex" style="margin-bottom:50px;">
    <div class="row justify-content-md-center">
        <div class="col-sm-5 foos-menu-selector" id="inp_btn" style="background-color:lightgray;"><h1 style="margin-top:10px;">In Progress</h1></div>
        <div class="col-sm-5 foos-menu-selector" id="final_btn"><h1 style="margin-top:10px;">Final</h1></div>
    </div>
</div>
<!-- inp singles matches -->
<div class="container-flex" id="inp_matches">
        <?php
        foreach ($inp_singles as $match_id) :
            $p1_id = get_post_meta($match_id, 'p1_id', true);
            $p2_id = get_post_meta($match_id, 'p2_id', true);
            $p1_score = get_post_meta($match_id, 'p1_score', true);
            $p2_score = get_post_meta($match_id, 'p2_score', true);
            $p1_user = get_userdata($p1_id);
            $p2_user = get_userdata($p2_id);
            $p1_name = $p1_user->display_name;
            $p2_name = $p2_user->display_name;
            $waiting = false;
            if ($p1_id == $curr_user_id && get_post_meta($match_id, 'p1_accept', true)) $waiting = true;
            if ($p2_id == $curr_user_id && get_post_meta($match_id, 'p2_accept', true)) $waiting = true;
            ?>
    <div class="row justify-content-md-center" style="margin-bottom: 50px;">
        <div class="col-sm-3">
            <div class="row">
                <div class="col-"><?php echo get_avatar($p1_id, 70) ?></div>
                <div class="col"><h3><?php echo $p1_name ?></h3></div>
            </div>
        </div>
        <div class="col-xs-3 foos-score-box-form" style="text-align:center;">
            <form method="post">
                <input type="hidden" name="match_id" value="<?php echo $match_id ?>">
                <h3><input type="text" name="p1_score" value="<?php echo $p1_score ?>" autocomplete="off">-<input type="text" name="p2_score" value="<?php echo $p2_score ?>" autocomplete="off"></h3>
                <?php if (!$waiting && ($p1_score == 5 xor $p2_score == 5)): ?>
                    <button type="submit" class="btn btn-success">Accept</button>
                <?php else: ?>
                    <button type="submit" class="btn btn-primary">Submit</button>
                <?php endif; ?>
                    
                <?php if ($submit_match_id == $match_id && !$valid_submit) :    // error message ?>
                    <p>You can't submit that score you crazy person!</p>
                <?php endif;
                if ($waiting) : ?>
                    <p>Waiting for opponent to accept</p>
                <?php endif; ?>
            </form>
        </div>
        <div class="col-sm-3" style="text-align:right;">
            <div class="row">
                <div class="col"><h3><?php echo $p2_name ?></h3></div>
                <div class="col-"><?php echo get_avatar($p2_id, 70) ?></div>
            </div>
        </div>
    </div>
            <?php
        endforeach; 
        if (count($inp_singles) == 0) : ?> 
    <div style='text-align: center;'>
        <h3>You aren't in any matches.</h3>
        <a class="et_pb_button et_pb_promo_button" href="<?php echo get_site_url().'/match-recorder/' ?>">Setup a match</a>
    </div>
        <?php endif; ?>
</div>
<!-- final matches -->
<div class="container-flex" id="final_matches" style='display:none;'>
    <?php
        foreach ($final_singles as $match_id) :
            $p1_id = get_post_meta($match_id, 'p1_id', true);
            $p2_id = get_post_meta($match_id, 'p2_id', true);
            $p1_user = get_userdata($p1_id);
            $p2_user = get_userdata($p2_id);
            $p1_name = $p1_user->display_name;
            $p2_name = $p2_user->display_name;
            $p1_score = get_post_meta($match_id, 'p1_score', true);
            $p2_score = get_post_meta($match_id, 'p2_score', true);
            ?>
    <div class="row justify-content-md-center" style="margin-bottom: 20px;">
        <div class="col-sm-3">
            <div class="row">
                <div class="col-"><?php echo get_avatar($p1_id, 70) ?></div>
                <div class="col"><h3><?php echo $p1_name ?></h3></div>
            </div>
        </div>
        <div class="col-sm-3" style="text-align:center;">
            <?php if (($p1_id == get_current_user_id() && $p1_score == 5) || ($p2_id == get_current_user_id() && $p2_score == 5)) : ?>
            <h3>W</h3> <?php else: ?> <h3>L</h3><?php endif; ?><h3><?php echo $p1_score ?> - <?php echo $p2_score ?></h3>
        </div>
        <div class="col-sm-3" style="text-align:right;">
            <div class="row">
                <div class="col"><h3><?php echo $p2_name ?></h3></div>
                <div class="col-"><?php echo get_avatar($p2_id, 70) ?></div>
            </div>
        </div>
    </div>
            <?php
        endforeach; ?>
</div>

<script>
    (function($) {
        $('.foos-score-box-form input').change(function() {
            var submit_btn = jQuery('.foos-score-box-form button');
            submit_btn.removeClass('btn-success');
            submit_btn.addClass('btn-primary');
            submit_btn.text('Submit');
        });
        $('.foos-score-box-form input').click(function() {this.select();});
        
        $('#inp_btn').click(function() {
            $(this).css('background-color', 'lightgray');
            $('#final_btn').css('background-color', '');
            $('#inp_matches').show();
            $('#final_matches').hide();
        });
        $('#final_btn').click(function() {
            $(this).css('background-color', 'lightgray');
            $('#inp_btn').css('background-color', '');
            $('#inp_matches').hide();
            $('#final_matches').show();
        });
    })(jQuery);
    
</script>
        <?php
        return ob_get_clean();
    }
    
    
    /**
     * Redirection function hooked into template_redirect. Checks for matchid parameter and finds existing match of
     * the player id parameters or creates new match with given player id parameters
     */
    public function create_singles_match($p1_id, $p2_id) {      
        $curr_user_id = get_current_user_id();
        // check if player ids exist
        // prevent other users from creating matches for other players
        // and from creating matches with themselves
        if (!$this->user_id_exists($p1_id) || !$this->user_id_exists($p2_id)
            || ($curr_user_id != $p1_id && $curr_user_id != $p2_id)
            || ($curr_user_id == $p1_id && $curr_user_id == $p2_id)) {
            return;
        }
        // check if match already exists
        $args = array(
            'post_type' => 'singles_match',
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'relation' => 'AND',
                    array(
                        'key' => 'p1_id',
                        'value' => $p1_id,
                        'compare' => '=',
                    ),
                    array(
                        'key' => 'p2_id',
                        'value' => $p2_id,
                        'compare' => '='
                    ),
                    array(
                        'key' => 'is_final',
                        'value' => 0,
                        'compare' => '='
                    )
                ),
                array(
                    'relation' => 'AND',
                    array(
                        'key' => 'p1_id',
                        'value' => $p2_id,
                        'compare' => '=',
                    ),
                    array(
                        'key' => 'p2_id',
                        'value' => $p1_id,
                        'compare' => '='
                    ),
                    array(
                        'key' => 'is_final',
                        'value' => 0,
                        'compare' => '='
                    )
                )
            )
        );
        $wp_query = new WP_Query($args);
        $existing_match_ids = $wp_query->get_posts();

        if (count($existing_match_ids) > 0) return;  // do not create new match if one already exists

        $p1_user = get_userdata($p1_id);
        $p2_user = get_userdata($p2_id);
        $p1_name = $p1_user->first_name . ' ' . $p1_user->last_name;
        $p2_name = $p2_user->first_name . ' ' . $p2_user->last_name;
        
        // create match post
        wp_insert_post(array(
            'post_type' => 'singles_match',
            'post_title' => $p1_name . ' vs. ' . $p2_name,
            'post_status' => 'publish',
            'meta_input' => array(
                'p1_id' => $p1_id,
                'p2_id' => $p2_id,
                'p1_score'  => 0,
                'p2_score'  => 0,
                'is_final' => 0,
                'p1_accept' => 0,
                'p2_accept' => 0
            )
        ));
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
        $wins = filter_var( get_user_meta( $user->ID, 'foos_wins', TRUE ), FILTER_VALIDATE_INT, $options );
        $losses = filter_var( get_user_meta( $user->ID, 'foos_losses', TRUE ), FILTER_VALIDATE_INT, $options );
        if ( $wins == -1 ) {
            $wins = 0;
        }
        if ( $losses == -1 ) {
            $losses = 0;
        }
        if ( $losses == 0 ) {
            return (float)($wins + 1) * $wins;
        }
        return ((float)$wins / (float)$losses) * ($wins + $losses);
    }
    
    private function get_user_inp_singles($user_id) {
        $singles_ids = new WP_Query([
            'post_type' => 'singles_match',
            'fields'    => 'ids',
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'relation' => 'AND',
                    array(
                        'key' => 'p1_id',
                        'value' => $user_id,
                        'compare' => '=',
                    ),
                    array(
                        'key' => 'is_final',
                        'value' => 0,
                        'compare' => '='
                    )
                ),
                array(
                    'relation' => 'AND',
                    array(
                        'key' => 'p2_id',
                        'value' => $user_id,
                        'compare' => '='
                    ),
                    array(
                        'key' => 'is_final',
                        'value' => 0,
                        'compare' => '='
                    )
                )
            )
        ]);
        return $singles_ids->posts;
    }
    
    private function get_user_final_singles($user_id) {
        $singles_ids = new WP_Query([
            'post_type' => 'singles_match',
            'fields'    => 'ids',
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'relation' => 'AND',
                    array(
                        'key' => 'p1_id',
                        'value' => $user_id,
                        'compare' => '=',
                    ),
                    array(
                        'key' => 'is_final',
                        'value' => 1,
                        'compare' => '='
                    )
                ),
                array(
                    'relation' => 'AND',
                    array(
                        'key' => 'p2_id',
                        'value' => $user_id,
                        'compare' => '='
                    ),
                    array(
                        'key' => 'is_final',
                        'value' => 1,
                        'compare' => '='
                    )
                )
            )
        ]);
        return $singles_ids->posts;
    }
    
    private function user_id_exists($user_id){
        global $wpdb;

        $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->users WHERE ID = %d", $user_id));

        if($count == 1){ return TRUE; }else{ return FALSE; }
    }
    
    private function submit_score($match_id, $p1_score, $p2_score) {
        if (get_post_meta($match_id, 'is_final', true)) return;    // cannot submit score of final match
        $curr_user_id = get_current_user_id();
        $p1_id = get_post_meta($match_id, 'p1_id', true);
        $p2_id = get_post_meta($match_id, 'p2_id', true);

        if (!$this->user_id_exists($p1_id) || !$this->user_id_exists($p2_id)
            || ($curr_user_id != $p1_id && $curr_user_id != $p2_id)
            || ($curr_user_id == $p1_id && $curr_user_id == $p2_id)) {
            return false;
        }

        // set current player
        if ($curr_user_id == $p1_id) {
            $curr_player = 'p1';
            $opp_player = 'p2';
        } else {
            $curr_player = 'p2';
            $opp_player = 'p1';
        }
       
        $prev_p1_score = get_post_meta($match_id, 'p1_score', true);
        $prev_p2_score = get_post_meta($match_id, 'p2_score', true);

        if (($prev_p1_score != $p1_score || $prev_p2_score != $p2_score)) { // change score
            if  (($p1_score == 5 && $p2_score == 5) || $p1_score < 0 || $p1_score > 5 || $p2_score < 0 || $p2_score > 5) {  // invalid score
                return false;
            }
            update_post_meta($match_id, 'p1_score', $p1_score);
            update_post_meta($match_id, 'p2_score', $p2_score);
            update_post_meta($match_id, $curr_player.'_accept', 1);
            update_post_meta($match_id, $opp_player.'_accept', 0);
            return true;
        }
        
        // current user accepts score
        update_post_meta($match_id, $curr_player.'_accept', 1);

        $p1_accept = get_post_meta($match_id, 'p1_accept', true);
        $p2_accept = get_post_meta($match_id, 'p2_accept', true);
        
        if ($p1_accept && $p2_accept && ($p1_score == 5 xor $p2_score == 5)) {  // finalize match
            update_post_meta($match_id, 'is_final', true);
            $this->update_player_data($p1_id, $p2_id, $p1_score, $p2_score);
            return true;
        }
    }
    
    /*
     * Precondition: scores are valid and final
     */
    private function update_player_data($p1_id, $p2_id, $p1_score, $p2_score) {
        // update career goals
        update_user_meta($p1_id, 'foos_g', intval(get_user_meta($p1_id, 'foos_g', TRUE)) + $p1_score);
        update_user_meta($p2_id, 'foos_g', intval(get_user_meta($p2_id, 'foos_g', TRUE)) + $p2_score);
        
        // update career goals allowed
        update_user_meta($p1_id, 'foos_ga', intval(get_user_meta($p1_id, 'foos_ga', TRUE)) + $p2_score);
        update_user_meta($p2_id, 'foos_ga', intval(get_user_meta($p2_id, 'foos_ga', TRUE)) + $p1_score);
        
        
        
        if ($p1_score == 5) {   // p1 is winner
            $winner_id = $p1_id;
            $loser_id = $p2_id;
        } else {    // p2 is winner
            $winner_id = $p2_id;
            $loser_id = $p1_id;
        }
        // update wins and losses
        update_user_meta($winner_id, 'foos_wins', intval(get_user_meta($winner_id, 'foos_wins', TRUE)) + 1);
        update_user_meta($loser_id, 'foos_losses', intval(get_user_meta($loser_id, 'foos_losses', TRUE)) + 1);

        // update current win streaks
        $curr_ws = intval(get_user_meta($winner_id, 'foos_ws', TRUE)) + 1;
        update_user_meta($winner_id, 'foos_ws', $curr_ws);
        update_user_meta($loser_id, 'foos_ws', 0);
        
        // update longest career wind streak
        if ($curr_ws > get_user_meta($winner_id, 'foos_lws', true)) {
            update_user_meta($winner_id, 'foos_lws', $curr_ws);
        }
    }
    
    private function get_avatar_url($get_avatar){
        preg_match('/src="(.*?)"/i', $get_avatar, $matches);
        return $matches[1];
    }
    
    private function foos_name($user) {
        $foos_name = $user->display_name;
        
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
        
        if ($user->ID == $all_players[0]->ID) {   // add gold medal
            $foos_name .= ' 🥇';
        } 
        if ($user->ID == $all_players[1]->ID) {   // add silver medal
            $foos_name .= ' 🥈';
        }
        if ($user->ID == $all_players[2]->ID) {   // add bronze medal
            $foos_name .= ' 🥉';
        }
        
        $max_streak = 0;
        $top_steakers = [];
        
        foreach ($all_players as $player) {
            $player_streak = get_user_meta($player->ID, 'foos_ws', true);   // get player win streak
            // process on fire data
            if ($player_streak > $max_streak) {
                $top_streakers = [$player->ID];
                $max_streak = $player_streak;
            } else if ($player_streak == $max_streak){
                array_push($top_streakers, $player->ID);
            }
        }
        
        foreach ($top_streakers as $streaker) {
            if ($max_streak > 2 && $streaker == $user->ID) $foos_name .= ' 🔥';
        }
        
        return $foos_name;
    }

 }

