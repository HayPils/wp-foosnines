<?php

/**
 * Description of class-foos-top-stats-board
 *
 * @author Hayden Pilsner
 */
class Foos_Top_Stats_Board {
    
    private $max_streak = 0;
    private $max_lws = 0;
    private $penult_lws = null;
    private $top_lws = [];
    private $top_streakers = [];
    
    public function __construct() {
        $this->get_top_lws();
        $this->get_top_streakers();
    }
    
    public function enqueue_js() {
        wp_enqueue_script( 'foos-top-stat-board', plugin_dir_url( __DIR__ ).'top-stats-board/top-stats-board.js', array('jquery', 'google-charts'), $this->version, true);   // enqueue js
    }
    
    private function get_top_lws() {
        $all_players = get_users( 'blog_id='.get_current_blog_id().'&orderby=nicename' );
        $this->max_lws = 0;
        $this->penult_lws = 0;
        $this->top_lws = [];
        
        foreach ($all_players as $player) {
            $player_lws = get_user_meta($player->ID, 'foos_lws', true);   // get player longest win streak
            
            // process longest win streak data
            if ($player_lws > $this->max_lws) {
                $this->top_lws = [$player];
                $this->max_lws = $player_lws;
            } else if ($player_lws == $this->max_lws) {
                array_push($this->top_lws, $player);
            }
            
            if ($this->max_lws > $player_lws && $this->penult_lws < $player_lws) $this->penult_lws = $player_lws;
            
        }
    }
    
    private function get_top_streakers() {
        $all_players = get_users( 'blog_id='.get_current_blog_id().'&orderby=nicename' );
        $this->max_streak = 0;
        $this->top_streakers = [];
        
        foreach ($all_players as $player) {
            $player_streak = get_user_meta($player->ID, 'foos_ws', true);   // get player win streak

            // process on fire data
            if ($player_streak > $this->max_streak) {
                $this->top_streakers = [$player];
                $this->max_streak = $player_streak;
            } else if ($player_streak == $this->max_streak){
                array_push($this->top_streakers, $player);
            }
        }
    }
    
    public function print_board() {
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
                        for ($i = 0; $i < count($this->top_lws); $i++) {
                            echo $this->top_lws[$i]->display_name;
                            echo ($i < count($this->top_lws) - 1) ? ', ' : ' ';
                        }
                    ?>
                    </h3>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <h3 style="margin-top:3px;"><?php echo ''.$this->max_lws.' wins'; ?></h3>
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
                        if ($this->max_streak > 2) {
                             for ($i = 0; $i < count($this->top_streakers); $i++) {
                                 echo $this->top_streakers[$i]->display_name;
                                 echo ($i < count($this->top_streakers) - 1) ? ', ' : ' ';
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
                    <?php if ($this->max_streak > 2) : ?>
                    <div id="on_fire_gauge" data-fire-cnt="<?php echo $this->max_streak ?>" data-lws="<?php echo $this->max_lws ?>" data-p-lws="<?php echo $this->penult_lws ?>"></div>
                    <?php endif; ?>
                </div>
            </div> 
        </div>
    </div>
</div>
        <?php  
    }
}
