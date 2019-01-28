<?php

/**
 * This is a class to preprocess information to display on player info pages.
 *
 * @author Hayden Pilsner
 */
class Foos_Player_Info {
    
    public function enqueue_scripts() {
        wp_enqueue_script( 'foos-player-info', plugin_dir_url( __DIR__ ).'public/js/player-info.js', array('jquery', 'google-charts'), $this->version, true);   // enqueue js
        wp_localize_script('foos-player-info', 'ajax_object', array('ajaxurl' => admin_url('admin-ajax.php')));
    }
    
    public function stats_portfolio($player_id) {
        $player = get_userdata($player_id);
        $wins = get_user_meta($player->ID, 'foos_wins', true);
        $wins = ($wins) ? $wins : 0;
        $losses = get_user_meta($player->ID, 'foos_losses', true);
        $losses = ($losses) ? $losses : 0;
        $wl_ratio = ($losses > 0) ? round($wins / $losses, 2) : 0;
        $ws = get_user_meta($player->ID, 'foos_ws', true);
        $ws = ($ws) ? $ws : 0;
        $lws = get_user_meta($player->ID, 'foos_lws', true);
        $lws = ($lws) ? $lws : 0;
        ?>
<div class="container-fluid">
    <div class="row">
        <div class="col">
            <?php echo get_avatar($player->ID, 150); ?>
            <h1><?php echo $this->foos_name($player) ?></h1>
            <h3><?php echo $player->first_name . ' ' . $player->last_name ?></h3>
            <h3>Score: <?php echo get_user_meta($player->ID, 'foos_elo', true) ?></h3>
        </div>
        <div class="col">
            <div class="row">
                <div class="col">
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">Wins</th>
                                    <th scope="col">Losses</th>
                                    <th scope="col">W/L Ratio</th>
                                    <th scope="col">G</th>
                                    <th scope="col">GA</th>
                                    <th scope="col">W Strk</th>
                                    <th scope="col">Longest W Strk</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?php echo $wins ?></td>
                                    <td><?php echo $losses ?></td>
                                    <td><?php echo $wl_ratio ?></td>
                                    <td><?php echo $this->get_career_goals($player->ID) ?></td>
                                    <td><?php echo $this->get_career_goals_allowed($player->ID) ?></td>
                                    <td><?php echo $ws ?></td>
                                    <td><?php echo $lws ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col">
                    <div id="elo_history_graph"></div>
                </div>
            </div>
        </div>
    </div>
</div>
        <?php
    }
    
}
