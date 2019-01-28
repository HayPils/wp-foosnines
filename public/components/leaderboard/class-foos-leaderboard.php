<?php

/**
 * This is a class to handle preprocessing and displaying the foos leaderboard
 *
 * @author Hayden Pilsner
 */
class class-foos-leaderboard {
    wp_enqueue_script( 'foos-leaderboard', plugin_dir_url( __DIR__ ).'public/js/leaderboard.js', array('jquery'), $this->version, true);   // enqueue js
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

        ob_start();
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
    $rank_counter = 1;
    // fill table with all players
    for ($i = 0; $i < $num_of_players; $i++ ) {
        $player = $all_players[$i];
        $player_wins = intval(get_user_meta($player->ID, 'foos_wins', TRUE));
        $player_losses = intval(get_user_meta($player->ID, 'foos_losses', TRUE));
        $wl_ratio = round(($player_losses == 0) ? $player_wins : (float)$player_wins / (float)$player_losses, 2);
        if ($player_wins + $player_losses != 0) : ?>
            <tr class="foos-leaderboard-row" data-player-id="<?php echo $player->ID ?>">
                <th scope="row" class="align-middle"><?php echo $rank_counter ?></td>
                <td style="padding-top:12px;" class="align-middle"><?php echo get_avatar($player->ID, 60) ?></td>
                <td class="align-middle"><?php echo $this->foos_name($player) ?></td>
                <td class="align-middle"><?php echo $player_wins ?></td>
                <td class="align-middle"><?php echo $player_losses ?></td>
                <td class="align-middle"><?php echo $wl_ratio ?></td>
                <td class="align-middle"><?php echo $this->rating($player->ID) ?></td>
            </tr>
            <?php 
            $rank_counter++;
        endif;
    }
    ?>
        </tbody>
    </table>
</div>
        <?php return ob_get_clean();
}
