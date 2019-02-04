<?php

/**
 * This class controls data access, creation and updating for foos players.
 *
 * @author Hayden Pilsner
 */
class Foos_Player_Controller {
    
    public function get_user_inp_singles($user_id, $order='DESC') {
        $singles_ids = new WP_Query([
            'post_type' => 'singles_match',
            'posts_per_page'    => -1,
            'fields'    => 'ids',
            'order'     => $order,
            'orderby'   => 'date',
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
    
    public function get_user_final_singles($user_id, $order='DESC') {
        $singles_ids = new WP_Query([
            'post_type' => 'singles_match',
            'posts_per_page'    => -1,
            'fields'    => 'ids',
            'order'     => $order,
            'meta_key'  => 'final_date',
            'orderby'   => 'meta_value_num',
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
    
    public function get_career_goals($user_id) {
        $singles_ids = $this->get_user_final_singles($user_id);
        $goal_sum = 0;
        foreach ($singles_ids as $match_id) {
            $p1_id = get_post_meta($match_id, 'p1_id', true);
            $p2_id = get_post_meta($match_id, 'p2_id', true);
            if ($user_id == $p1_id) {
                $goal_sum += get_post_meta($match_id, 'p1_score', true);
            } else {
                $goal_sum += get_post_meta($match_id, 'p2_score', true);
            }
        }
        return $goal_sum;
    }
    
    public function get_career_goals_allowed($user_id) {
        $singles_ids = $this->get_user_final_singles($user_id);
        $ga_sum = 0;
        foreach ($singles_ids as $match_id) {
            $p1_id = get_post_meta($match_id, 'p1_id', true);
            $p2_id = get_post_meta($match_id, 'p2_id', true);
            if ($user_id == $p1_id) {
                $ga_sum += get_post_meta($match_id, 'p2_score', true);
            } else {
                $ga_sum += get_post_meta($match_id, 'p1_score', true);
            }
        }
        return $ga_sum;
    }
    
    /*
     * Precondition: scores are valid and final
     */
    public function update_player_data($p1_id, $p2_id, $p1_score, $p2_score, $match_id) {
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
        
        $elo_cont = new Foos_Elo_Controller();
        // update elo rating (pre-condition: match is final)
        $elo_cont->update_elo_from_match($match_id);
    }
    
    public function get_players_by($order) {
        $curr_blog_id = get_current_blog_id();
        // players to display in rows on leader board in ranked order
        $all_players = get_users( 'blog_id='.$curr_blog_id.'&orderby=nicename' );

        switch ($order) :
            case 'score':
                for ($i = 1; $i < count($all_players); $i++) {
                $index_shadow = $i;
                while ( $index_shadow > 0 && $this->rating($all_players[$index_shadow - 1]->ID) < $this->rating($all_players[$index_shadow]->ID) ) {
                    $temp = $all_players[$index_shadow - 1]; // update previous player
                    $all_players[$index_shadow - 1] = $all_players[$index_shadow]; // swap lower ranked player back
                    $all_players[$index_shadow] = $temp;    // swap higher ranked player ahead
                    $index_shadow--;
                }
            }
            break;
            // add more cases for different order values
        endswitch;

        return $all_players;
    }
    
    /**
     * Returns rank of a player based on W/L ratio multiplied by
     * number of played games
     *
     * @param $user   player to rank
     * @return bool|float|int   FALSE if player stats are not integers, else
     *                          returns player rank as float or int
     */
    public function rating( $user_id ) {
        $BASE_ELO = 1000;
        $user_elo = get_user_meta($user_id, 'foos_elo', true);
        if (!$user_elo) {
            update_user_meta($user_id, 'foos_elo', $BASE_ELO);
        }
        $wins = get_user_meta($user_id, 'foos_wins', true);
        $losses = get_user_meta($user_id, 'foos_losses', true);
        return ($wins + $losses > 0) ? $user_elo : 0;
    }
}
