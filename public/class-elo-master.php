<?php

/**
 * A class to handle getting, and setting Elo rankings for players
 *
 * @author Hayden Pilsner
 */
class Elo_Master {
    public function __construct() {}
    
    public function update_all_elo() {
        $match_master = new Match_Master();
        $BASE_ELO = 1000;
        $all_players = get_users();
        
        foreach ($all_players as $player) { // reset all elos to base elo
            update_user_meta($player->ID, 'foos_elo', $BASE_ELO);
        }
        $final_match_ids = $match_master->get_all_final_singles('ASC');
        foreach ($final_match_ids as $match_id ) {
            $this->update_elo_from_match($match_id);
        }
    }
    
    public function update_elo_from_match($match_id) {
        if (!get_post_meta($match_id, 'is_final', true)) return;
        // get user ratings
        $p1_id = get_post_meta($match_id, 'p1_id', true);
        $p2_id = get_post_meta($match_id, 'p2_id', true);
        $p1_rating = get_user_meta($p1_id, 'foos_elo', true);
        $p2_rating = get_user_meta($p2_id, 'foos_elo', true);
        
        if (get_post_meta($match_id, 'p1_score', true) == 5) {  // p1 won
            $p1_actual = 1;
            $p2_actual = 0;
            
            // BONUS POINTS
            $p1_new_rating = 10;
            $p2_new_rating = 0;
        } else {    // p2 won
            $p1_actual = 0;
            $p2_actual = 1;
            
            // BONUS POINTS
            $p1_new_rating = 0;
            $p2_new_rating = 10;
        }
        
        // calc user ratings
        $p1_prob = $this->winning_chance($p1_rating, $p2_rating);
        $p2_prob = 1 - $p1_prob;
        $p1_new_rating += round($p1_rating + $this->k_factor($p1_rating) * ($p1_actual - $p1_prob), 0);
        $p2_new_rating += round($p2_rating + $this->k_factor($p2_rating) * ($p2_actual - $p2_prob), 0);
        
        update_user_meta($p1_id, 'foos_elo', $p1_new_rating);
        update_user_meta($p2_id, 'foos_elo', $p2_new_rating);
    }
    
    public function winning_chance($my_rating, $opp_rating) {
        return 1 / (1 + pow(10, ($opp_rating - $my_rating) / 400));
    }
        
    private function k_factor($rating) {
        if ($rating < 2100) return 32;
        if ($rating >= 2100 && $rating <= 2400) return 24;
        if ($rating > 2400) return 16;
    }
    
    public function get_elo_history($player_id) {
        $match_master = new Match_Master();
        
        $BASE_ELO = 1000;
        $all_players = get_users();
        $player_elo = [];
        $elo_history = [1000];
        
        foreach ($all_players as $player) { // reset all elos to base elo (beginning of history)
            $player_elo[$player->ID] = $BASE_ELO;
        }
        
        $final_singles_ids = $match_master->get_all_final_singles('ASC');
        foreach ($final_singles_ids as $match_id) {
            if (!get_post_meta($match_id, 'is_final', true)) return;
            // get user ratings
            $p1_id = get_post_meta($match_id, 'p1_id', true);
            $p2_id = get_post_meta($match_id, 'p2_id', true);
            $p1_rating = $player_elo[$p1_id];
            $p2_rating = $player_elo[$p2_id];

            if (get_post_meta($match_id, 'p1_score', true) == 5) {  // p1 won
                $p1_actual = 1;
                $p2_actual = 0;
                $p1_new_rating = 10;
                $p2_new_rating = 0;
            } else {    // p2 won
                $p1_actual = 0;
                $p2_actual = 1;
                $p1_new_rating = 0;
                $p2_new_rating = 10;
            }

            // calc user ratings
            $p1_prob = $this->winning_chance($p1_rating, $p2_rating);
            $p2_prob = 1 - $p1_prob;
            $p1_new_rating += round($p1_rating + $this->k_factor($p1_rating) * ($p1_actual - $p1_prob), 0);
            $p2_new_rating += round($p2_rating + $this->k_factor($p2_rating) * ($p2_actual - $p2_prob), 0);

            $player_elo[$p1_id] = $p1_new_rating;
            $player_elo[$p2_id] = $p2_new_rating;
            if ($p1_id == $player_id) {
                array_push($elo_history, $p1_new_rating);
            }
            if ($p2_id == $player_id) {
                array_push($elo_history, $p2_new_rating);
            }
        }
        return $elo_history;
    }
}
