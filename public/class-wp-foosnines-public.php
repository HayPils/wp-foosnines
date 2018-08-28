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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-foosnines-public.js', array( 'jquery' ), $this->version, false );
                wp_enqueue_script( 'bootstrap_js', "https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js", array('jquery'), $this->version, true);

	}

    // -------------------- ACTION CALLBACKS ----------------------

    public function deactivate_foos() {
        flush_rewrite_rules(TRUE);
    }

    public function create_match_post_type() {
	    $labels = array(
            'name'               => __( 'Matches' ),
            'singular_name'      => __( 'Match' ),
        );

        $args = array(
            'labels'             => $labels,
            'description'        => __( 'Match post type for recording foosball match data' ),
            'public'             => TRUE,
            'publicly_queryable' => TRUE,
            'show_ui'            => TRUE,
            'show_in_menu'       => TRUE,
            'query_var'          => TRUE,
            'has_archive'        => TRUE,
            'rewrite'            => array('slug'    =>  'matches'),
            'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments')
        );
        register_post_type( 'match', $args);

        register_meta( 'match', 'player1_id', array(
            'type'          => 'number',
            'single'        => TRUE,
        ));
        register_meta( 'match', 'player2_id', array(
            'type'          => 'number',
            'single'        => TRUE,
        ));
        register_meta( 'match', 'player3_id', array(
            'type'          => 'number',
            'single'        => TRUE,
        ));
        register_meta( 'match', 'player4_id', array(
            'type'          => 'number',
            'single'        => TRUE,
        ));
        register_meta( 'match', 'is_final', array(
            'type'          => 'boolean',
            'single'        => TRUE,
        ));
        register_meta( 'match', 'team1_score1', array(
            'type'          => 'integer',
            'single'        => TRUE,
        ));
        register_meta( 'match', 'team2_score1', array(
            'type'          => 'integer',
            'single'        => TRUE,
        ));
        register_meta( 'match', 'team1_score2', array(
            'type'          => 'integer',
            'single'        => TRUE,
        ));
        register_meta( 'match', 'team2_score2', array(
            'type'          => 'integer',
            'single'        => TRUE,
        ));
    }

    /**
     * Redirection function hooked into template_redirect. Checks for matchid parameter and finds existing match of
     * the player id parameters or creates new match with given player id parameters
     */
    public function foos_matches_redirect() {
        if (is_post_type_archive('match')) {
            // user is accessing new match or potentially existing match
            if (isset($_POST['p1id']) && isset($_POST['p2id'])) {
                $current_user_id = get_current_user_id();
                $p1_id = intval($_POST['p1id']);
                $p2_id = intval($_POST['p2id']);

                // check if player ids exist
                // prevent other users from creating matches for other players
                // and from creating matches with themselves
                if (!$this->user_id_exists($p1_id) || !$this->user_id_exists($p2_id)
                    || ($current_user_id != $p1_id && $current_user_id != $p2_id)
                    || ($current_user_id == $p1_id && $current_user_id == $p2_id)) {
                    return;
                }

                // check if match already exists
                $args = array(
                    'post_type' => 'match',
                    'meta_query' => array(
                        'relation' => 'OR',
                        array(
                            'relation' => 'AND',
                            array(
                                'key' => 'player1_id',
                                'value' => $p1_id,
                                'compare' => '=',
                            ),
                            array(
                                'key' => 'player3_id',
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
                                'key' => 'player1_id',
                                'value' => $p2_id,
                                'compare' => '=',
                            ),
                            array(
                                'key' => 'player3_id',
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
                $existing_match_id = $wp_query->get_posts();

                if (count($existing_match_id) > 0) {
                    wp_redirect(get_permalink($existing_match_id[0]->ID));
                    exit;
                }

                $p1_user = get_userdata($p1_id);
                $p2_user = get_userdata($p2_id);

                $p1_name = $p1_user->first_name . ' ' . $p1_user->last_name;
                $p2_name = $p2_user->first_name . ' ' . $p2_user->last_name;

                // create match post
                $new_match_id = wp_insert_post(array(
                    'post_type' => 'match',
                    'post_title' => $p1_name . ' vs. ' . $p2_name,
                    'post_status' => 'publish',
                    'meta_input' => array(
                        'player1_id' => $p1_id,
                        'player3_id' => $p2_id,
                        'is_final' => 0,
                        'team1_score1' => 0,
                        'team2_score1' => 0,
                        'team1_score2' => 0,
                        'team2_score2' => 0
                    )
                ));

                wp_update_post(array(
                    'ID' => $new_match_id,
                    'post_name' => $new_match_id
                ));

                // query for custom post type
                wp_redirect(get_permalink($new_match_id));
                exit;

            }
        }
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

    // -------------------- FILTER CALLBACKS ----------------------

    public function foos_match_page_body($content) {
        if (is_single() && get_post_type() == 'match') {    // user sucessfully directed to a match post page
            $match_post = get_post();
            $match_id = $match_post->ID;

            $match_p1 = get_post_meta($match_id, 'player1_id', TRUE);
            $match_p2 = get_post_meta($match_id, 'player3_id', TRUE);
            $current_user_id = get_current_user_id();

            $player_1_user = get_userdata($match_p1);
            $player_2_user = get_userdata($match_p2);

            $player_1_name = $player_1_user->first_name . ' ' . $player_1_user->last_name;
            $player_2_name = $player_2_user->first_name . ' ' . $player_2_user->last_name;

            $p1_avatar_url = get_avatar_url($match_p1);
            $p2_avatar_url = get_avatar_url($match_p2);

            // HTML elements
            $team1_score_box = '';
            $team2_score_box = '';
            $submit_button = '';
            $status_message = '';

            if (get_post_meta($match_id, 'is_final', TRUE)) {   // only display results if match is final
                $team1score = get_post_meta($match_id, 'team1_score1', TRUE);
                $team2score = get_post_meta($match_id, 'team2_score1', TRUE);
                $team1_score_box = '<h5>' . $team1score . '</h5>';
                $team2_score_box = '<h5>' . $team2score . '</h5>';
                if ($team1score == 5) {
                    $status_message = 'Winner: ' . $player_1_name;
                } else {
                    $status_message = 'Winner: ' . $player_2_name;
                }
            } else {    // Match is not final display logic
                if ($match_p1 == $current_user_id) {    // current user is player 1
                    $team1score = get_post_meta($match_id, 'team1_score1', TRUE);
                    $team2score = get_post_meta($match_id, 'team2_score1', TRUE);
                    $status_message = $this->process_scores($match_id);
                    if (isset($_POST['team1score']) && isset($_POST['team2score'])) {   // player has submitted a score
                        $team1score = intval($_POST['team1score']);
                        $team2score = intval($_POST['team2score']);
                        if ($this->validate_scores($team1score, $team2score)) {
                            update_post_meta($match_id, 'team1_score1', $team1score);
                            update_post_meta($match_id, 'team2_score1', $team2score);
                            $status_message = $this->process_scores($match_id);
                        } else {
                            $team1score = get_post_meta($match_id, 'team1_score1', TRUE);
                            $team2score = get_post_meta($match_id, 'team2_score1', TRUE);
                            $status_message = 'Scores must be greater than or equal to 0 and less than or equal to 5; no ties.';
                        }
                    }

                } else if ($match_p2 == $current_user_id) {     // current user is player 2
                    $team1score = get_post_meta($match_id, 'team1_score2', TRUE);
                    $team2score = get_post_meta($match_id, 'team2_score2', TRUE);
                    $status_message = $this->process_scores($match_id);
                    if (isset($_POST['team1score']) && isset($_POST['team2score'])) {   // player has submitted a score
                        $team1score = intval($_POST['team1score']);
                        $team2score = intval($_POST['team2score']);
                        if ($this->validate_scores($team1score, $team2score)) {
                            update_post_meta($match_id, 'team1_score2', $team1score);
                            update_post_meta($match_id, 'team2_score2', $team2score);
                            $status_message = $this->process_scores($match_id);
                        } else {
                            $team1score = get_post_meta($match_id, 'team1_score2', TRUE);
                            $team2score = get_post_meta($match_id, 'team2_score2', TRUE);
                            $status_message = 'Scores must be greater than or equal to 0 and less than or equal to 5; no ties.';
                        }
                    }
                }
                if ($match_p1 == $current_user_id || $match_p2 == $current_user_id) {
                    if (get_post_meta($match_id, 'is_final', TRUE)) {
                        // DISPLAY FINAL SCORES AND WINNER
                        $team1_score_box = '<h5>' . $team1score . '</h5>';
                        $team2_score_box = '<h5>' . $team2score . '</h5>';
                        if ($team1score == 5) {
                            $status_message = 'Winner: ' . $player_1_name;
                        } else {
                            $status_message = 'Winner: ' . $player_2_name;
                        }
                    } else {
                        // DISPLAY SCORE SUBMISSION BOXES
                        $team1_score_box = '<input type="text" class="form-control" name="team1score" value="' . $team1score . '" onclick="this.select()">';
                        $team2_score_box = '<input type="text" class="form-control" name="team2score" value="' . $team2score . '" onclick="this.select()">';
                        $submit_button = '<div class="row" style="padding-top: 50px">
                                            <button type="submit" class="btn btn-primary" style="margin: auto;">Submit Score</button>
                                          </div>';
                    }
                } else {    // display spectator page
                    // TODO: DISPLAY SPECTATOR PAGE
                    $status_message = 'Match in progress';
                }
            }

            // includes updated win/loss stats if match is completed
            $player_1_wins = get_user_meta($match_p1, 'wins', TRUE);
            $player_1_losses = get_user_meta($match_p1, 'losses', TRUE);

            $player_2_wins = get_user_meta($match_p2, 'wins', TRUE);
            $player_2_losses = get_user_meta($match_p2, 'losses', TRUE);


            return '<form method="post">
                        <div class="row">
                            <div class="col" style="text-align:center">
                                <img src="' . $p1_avatar_url . '" alt="' . $player_1_name . ' \'s avatar" 
                                style="margin-left: auto; margin-right: auto; display: block">
                                <h5 id="player_1_name" style="padding-top: 20px;">' . $player_1_name . '</h5>
                                <div class="row justify-content-center" style="padding-top: 10px;">
                                    <div class="col-4">Wins: ' . $player_1_wins . '</div>
                                    <div class="col-4">Losses: ' . $player_1_losses . '</div>
                                </div>
                                <div class="row justify-content-center" style="padding-top: 10px;">
                                    <div class="col-2">
                                        <label for="score2">Score:</label>'
                                            . $team1_score_box .
                                    '</div>
                                </div>
                            </div>
                            <div class="col" style="text-align:center">
                                <img src="' . $p2_avatar_url . '" alt="' . $player_2_name . ' \'s avatar" 
                                style="margin-left: auto; margin-right: auto; display: block">
                                <h5 style="padding-top: 20px;">' . $player_2_name . '</h5>
                                <div class="row justify-content-center" style="padding-top: 10px;">
                                    <div class="col-4">Wins: ' . $player_2_wins . '</div>
                                    <div class="col-4">Losses: ' . $player_2_losses . '</div>
                                </div>
                                <div class="row justify-content-center" style="padding-top: 10px">
                                    <div class="col-2">
                                        <label for="score2">Score:</label>'
                                            . $team2_score_box .
                                    '</div>
                                </div>
                            </div>                         
                        </div>'
                            . $submit_button .
                        '<div class="row" style="padding-top: 50px;"><h3 style="margin: auto;">'
                            . $status_message .
                        '</h3></div>
                    </form>';

        }
        return $content;
    }

    // -------------------- HELPER FUNCTIONS ----------------------

    function user_id_exists($user_id){
        global $wpdb;

        $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->users WHERE ID = %d", $user_id));

        if($count == 1){ return TRUE; }else{ return FALSE; }
    }

    /**
     * A super effective way to destroy someone's ego when they try to $%@! with your site.
     *
     * @param $message      string     A public announcement to destroy someones reputation
     */
    private function counter_attack( $message ) {
        $current_user_id = get_current_user_id();
        update_user_meta($current_user_id, 'losses', intval(get_user_meta($current_user_id, 'losses', TRUE)) + 1);
        update_user_meta(2, wins, intval(get_user_meta(2, 'wins', TRUE)) + 1);
        // TODO: add as match to public matches board with message for public humiliation
    }

    private function validate_scores($team1score, $team2score) {
        if ($team1score < 0 || $team1score > 5 || ($team1score == $team2score && $team1score == 5)) {
            return FALSE;
        }
        if ($team2score < 0 || $team2score > 5) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Precondition: match exists and all team scores exist and are valid.
     *
     * Finalizes a match if a winner has been declared (5 pts) and both submissions are equivalent
     *
     * @param   $match_id    match to finalize if scores match
     * @return  string      status message of the match based on the current scores
     */
    private function process_scores($match_id) {
        $team1score1 = get_post_meta($match_id, 'team1_score1', TRUE);
        $team2score1 = get_post_meta($match_id, 'team2_score1', TRUE);
        $team1score2 = get_post_meta($match_id, 'team1_score2', TRUE);
        $team2score2 = get_post_meta($match_id, 'team2_score2', TRUE);
        $p1_id = get_post_meta($match_id, 'player1_id', TRUE);
        $p2_id = get_post_meta($match_id, 'player3_id', TRUE);

        // scores have been matched
        if ($team1score1 == $team1score2 && $team2score1 == $team2score2) {
            if ($team1score1 == 5 && $team2score1 == 5) {    // cannot end in tie
                return 'Match cannot end in a tie!';
            }
            // finalize match
            if ($team1score1 == 5) {
                update_post_meta($match_id, 'is_final', 1);
                update_user_meta($p1_id, 'wins', intval(get_user_meta($p1_id, 'wins', TRUE)) + 1);
                update_user_meta($p2_id, 'losses', intval(get_user_meta($p2_id, 'losses', TRUE)) + 1);
                wp_update_post( array (
                        'ID'            => $match_id,
                        'post_title'    => get_post( $match_id )->post_title . ' (' . $team1score1 . ' - ' . $team2score1 . ')',
                        'post_content'  => 'Winner: ' . get_userdata($p1_id)->first_name . ' ' . get_userdata($p1_id)->last_name
                    )
                );
            }
            if ($team2score1 == 5) {
                update_post_meta($match_id, 'is_final', 1);
                update_user_meta($p2_id, 'wins', intval(get_user_meta($p2_id, 'wins', TRUE)) + 1);
                update_user_meta($p1_id, 'losses', intval(get_user_meta($p1_id, 'losses', TRUE)) + 1);
                wp_update_post( array (
                        'ID'            => $match_id,
                        'post_title'    => get_post( $match_id )->post_title . ' (' . $team1score1 . ' - ' . $team2score1 . ')',
                        'post_content'  => 'Winner: ' . get_userdata($p2_id)->first_name . ' ' . get_userdata($p2_id)->last_name
                    )
                );
            }
            return 'Submit your match\'s current score.';
        }
        if ($team1score1 == 0 && $team2score1 == 0 && $team1score2 == 0 && $team2score2 == 0) {
            return 'Submit your match\'s current score.';
        }
        if (($team1score1 == 5 || $team2score1 == 5) xor ($team1score2 == 5 || $team2score2 == 5)) {
            if (get_current_user_id() == $p1_id && ($team1score1 == 5 || $team2score1 == 5)) {
                return 'Score submitted! Waiting on your opponent\'s submission.';
            }
            if (get_current_user_id() == $p2_id && ($team1score2 == 5 || $team2score2 == 5)) {
                return 'Score submitted! Waiting on your opponent\'s submission.';
            }
        }
        if ($team1score1 == 5 || $team2score1 == 5 || $team1score2 == 5 || $team2score2 == 5) { //if finalizing match, scores must match
            return 'Score must match your opponent\'s score!';
        }
        return 'Score submitted! Continue the game and resubmit final score later.';
    }

}

