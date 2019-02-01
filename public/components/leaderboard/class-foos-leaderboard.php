<?php

/**
 * This is a class to handle preprocessing and displaying the foos leaderboard
 *
 * @author Hayden Pilsner
 */
class Foos_Leaderboard {
    
    public function enqueue_js() {
        wp_enqueue_script( 'foos-leaderboard', plugin_dir_url( __DIR__ ).'leaderboard.js', array('jquery'), $this->version, true);   // enqueue js
    }

    public function print_leaderboard($max) {
        $player_controller = new Foos_Player_Controller();
        $all_players = $player_controller->get_players_by('score');
        ?>
<div class="table-responsive">
    <table class="table table-hover">
        <thead class="thead-dark">
            <tr>
                <th scope="col">Rank</th>
                <th scope="col"></th>
                <th scope="col"></th>
                <th scope="col">Wins</th>
                <th scope="col">Losses</th>
                <th scope="col">W/L Ratio</th>
                <th scope="col"><a href='https://en.wikipedia.org/wiki/Elo_rating_system'>Score</a></th>
            </tr>
        <thead>
        <tbody>
    <?php
    // fill table with all players
    $max = ($max == -1) ? count($all_players) : $max;
    for ($i = 0; $i < $max; $i++ ) {
        $this->print_player($all_players[$i], $i + 1);
    }
    ?>
        </tbody>
    </table>
</div>
        <?php
    }
    
    private function print_player($player, $rank) {
        $player_wins = intval(get_user_meta($player->ID, 'foos_wins', TRUE));
        $player_losses = intval(get_user_meta($player->ID, 'foos_losses', TRUE));
        $player_elo = get_user_meta($player->ID, 'foos_elo', TRUE);
        $wl_ratio = round(($player_losses == 0) ? $player_wins : (float)$player_wins / (float)$player_losses, 2);
        if ($player_wins + $player_losses != 0) : ?>
            <tr class="foos-leaderboard-row" data-player-id="<?php echo $player->ID ?>">
                <th scope="row" class="align-middle"><?php echo $rank ?></td>
                <td style="padding-top:12px;" class="align-middle"><?php echo get_avatar($player->ID, 60) ?></td>
                <td class="align-middle"><?php echo Foos_Info_Filter::foos_name($player) ?></td>
                <td class="align-middle"><?php echo $player_wins ?></td>
                <td class="align-middle"><?php echo $player_losses ?></td>
                <td class="align-middle"><?php echo $wl_ratio ?></td>
                <td class="align-middle"><?php echo $player_elo ?></td>
            </tr>
            <?php 
        endif;
    }
}
