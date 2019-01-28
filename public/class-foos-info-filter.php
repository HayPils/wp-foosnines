<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * This class handles cosmetic filtering for information before its printed
 * to pages. This class is stateless, all methods must be functional.
 *
 * @author Hayden Pilsner
 */
class Foos_Info_Filter {
    private static function foos_name($user) {
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
            while ( $index_shadow > 0 && $this->rating($all_players[$index_shadow - 1]->ID) < $this->rating($all_players[$index_shadow]->ID) ) {
                $temp = $all_players[$index_shadow - 1]; // update previous player
                $all_players[$index_shadow - 1] = $all_players[$index_shadow]; // swap lower ranked player back
                $all_players[$index_shadow] = $temp;    // swap higher ranked player ahead
                $index_shadow--;
            }
        }
        
        // add medals
        if ($user->ID == $all_players[0]->ID) {   // add gold medal
            $foos_name .= ' 🥇';
        } 
        if ($user->ID == $all_players[1]->ID) {   // add silver medal
            $foos_name .= ' 🥈';
        }
        if ($user->ID == $all_players[2]->ID) {   // add bronze medal
            $foos_name .= ' 🥉';
        }
        
        
        // add fire
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
        
        // add lightning (lws)
        $max_lws = 0;
        $top_lws = [];
        
        foreach ($all_players as $player) {
            $player_lws = get_user_meta($player->ID, 'foos_lws', true);   // get player longest win streak
            // process longest win streak data
            if ($player_lws > $max_lws) {
                $top_lws = [$player->ID];
                $max_lws = $player_lws;
            } else if ($player_lws == $max_lws) {
                array_push($top_lws, $player->ID);
            }
        }
        foreach ($top_lws as $top_lws_player) {
            if ($top_lws_player == $user->ID) $foos_name .= ' ⚡';
        }
        
        return $foos_name;
    }
    
    private static function foos_date($unix_time) {
        $now_week = intval(current_time('W'));
        $arg_week = intval(date('W', $unix_time));
        if ($now_week == $arg_week) return date('D, g:ia', $unix_time);
        return date('M j', $unix_time);
    }
    
    private static function foos_score_display($match_id, $tag='h3') {
        $p1_score = get_post_meta($match_id, 'p1_score', true);
        $p2_score = get_post_meta($match_id, 'p2_score', true);
        $score_display = '<'.$tag.'>';
        if ($p1_score == 5) {
            $score_display .= '<span style="color: #FF7800;">'.$p1_score.'</span>';
            $score_display .= ' - '.$p2_score;
        } else if ($p2_score == 5) {
            $score_display .= $p1_score;
            $score_display .= ' - <span style="color: #FF7800;">'.$p2_score.'</span>';
        }
        return $score_display . '</'.$tag.'>';
    }
}
