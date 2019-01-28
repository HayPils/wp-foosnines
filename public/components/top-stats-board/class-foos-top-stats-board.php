<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of class-foos-top-stats-board
 *
 * @author haydenpilsner
 */
class class-foos-top-stats-board {
wp_enqueue_script( 'foos-top-stat-board', plugin_dir_url( __DIR__ ).'public/js/top-stat-board.js', array('jquery', 'google-charts'), $this->version, true);   // enqueue js
        ob_start();
        $all_players = get_users( 'blog_id='.$curr_blog_id.'&orderby=nicename' );
        $max_streak = 0;
        $max_lws = 0;
        $penult_lws = 0;
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
            
            if ($max_lws > $player_lws && $penult_lws < $player_lws) $penult_lws = $player_lws;

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
        <div class="col" style="text-align:center;">
            <div class="row">
                <div class="col">
                    <h2>Record Win Streak ⚡ </h2>
                </div>
            </div>
            <div class="row">
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
            </div>
            <div class="row">
                <div class="col">
                    <h3 style="margin-top:3px;"><?php echo ''.$max_lws.' wins'; ?></h3>
                </div>
            </div>
        </div>

        <!-- Longest win streak (On fire >= 3 wins) -->
        <div class="col" style="text-align:center;">
            <div class="row">        
                <div class="col justify-content-md-center">
                    <h2>On Fire 🔥</h2>
                </div>
            </div>
            <div class="row">
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
            </div>
            <div class="row">
                <div class="col">
                    <?php if ($max_streak > 2) : ?>
                    <div id="on_fire_gauge" data-fire-cnt="<?php echo $max_streak ?>" data-lws="<?php echo $max_lws ?>" data-p-lws="<?php echo $penult_lws ?>"></div>
                    <?php endif; ?>
                </div>
            </div> 
        </div>
    </div>
</div>
        <?php
        return ob_get_clean();
}
