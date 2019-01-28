<?php

/**
 * A component preprocesses class for the my-matches page
 *
 * @author Hayden Pilsner
 */
class Foos_My_Matches {
    private $valid_submit = true;
    private $curr_user = null;
    
    public function __construct() {
        $this->c_user = get_current_user();
    }
    
    public function inp_singles_match_list() {
        $inp_singles = $this->get_user_inp_singles($curr_user_id);
        ?>
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
            $p1_name = $this->foos_name($p1_user);
            $p2_name = $this->foos_name($p2_user);
            $p1_chance = round($elo_master->winning_chance(get_user_meta($p1_id, 'foos_elo', true), get_user_meta($p2_id, 'foos_elo', true)), 2) * 100;
            $p2_chance = 100 - $p1_chance;
            $waiting = false;
            if ($p1_id == $curr_user_id && get_post_meta($match_id, 'p1_accept', true)) $waiting = true;
            if ($p2_id == $curr_user_id && get_post_meta($match_id, 'p2_accept', true)) $waiting = true;
            ?>
    <div class="row justify-content-md-center foos-match-row">
        <div class="col-sm-5">
            <div class="row">
                <div class="col-"><?php echo get_avatar($p1_id, 80) ?></div>
                <div class="col">
                    <div class="row">
                        <div class="col"><h3><?php echo $p1_name ?></h3></div>
                    </div>
                    <div class="row">
                        <div class="col"><?php echo get_user_meta($p1_id, 'foos_elo', true) ?></div>
                    </div>
                    <div class="row">
                        <div class="col">Chance: <?php echo $p1_chance ?>%</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-2 foos-score-box-form" style="text-align:center;">
            <form method="post">
                <input type="hidden" name="match_id" value="<?php echo $match_id ?>">
                <h3><input type="text" name="p1_score" value="<?php echo $p1_score ?>" class="foos-score-box" autocomplete="off">
                    -
                    <input type="text" name="p2_score" value="<?php echo $p2_score ?>" class="foos-score-box" autocomplete="off"></h3>
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
        <div class="col-sm-5" style="text-align:right;">
            <div class="row">
                <div class="col">
                    <div class="row">
                        <div class="col"><h3><?php echo $p2_name ?></h3></div>
                    </div>
                    <div class="row">
                        <div class="col"><?php echo get_user_meta($p2_id, 'foos_elo', true) ?></div>
                    </div>
                    <div class="row">
                        <div class="col">Chance: <?php echo $p2_chance ?>%</div>
                    </div>
                </div>
                <div class="col-"><?php echo get_avatar($p2_id, 80) ?></div>
            </div>
        </div>
    </div>
</div>
            <?php endforeach;
    }
    
    public function final_singles_match_list() {
        ?>
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
            $final_date = $this->foos_date(intval(get_post_meta($match_id, 'final_date', true)));
            ?>
    <div class="row justify-content-md-center foos-match-row">
        <div class="col-sm-4">
            <div class="row">
                <div class="col-"><?php echo get_avatar($p1_id, 80) ?></div>
                <div class="col"><h3><?php echo $p1_name ?></h3></div>
            </div>
        </div>
        <div class="col-sm-3" style="text-align:center;">
            <?php if (($p1_id == get_current_user_id() && $p1_score == 5) || ($p2_id == get_current_user_id() && $p2_score == 5)) : ?>
            <h4>W</h4> <?php else: ?> <h4>L</h4><?php endif; echo $this->foos_score_display($match_id) ?>
            <?php echo $final_date ?>
        </div>
        <div class="col-sm-4" style="text-align:right;">
            <div class="row">
                <div class="col"><h3><?php echo $p2_name ?></h3></div>
                <div class="col-"><?php echo get_avatar($p2_id, 80) ?></div>
            </div>
        </div>
    </div>
            <?php endforeach; ?>
</div>
        <?php
    }
            
    public function my_matches() {
        $match_master = new Match_Master();
        $elo_master = new Elo_Master();
        
        wp_enqueue_script( 'foos-my-matches', plugin_dir_url( __DIR__ ).'public/js/my-matches.js', array('jquery'), $this->version, true);   // enqueue js
        wp_localize_script('foos-my-matches', 'ajax_object', array('ajaxurl' => admin_url('admin-ajax.php')));
        
        $curr_user_id = get_current_user_id();
        $curr_user = get_userdata($curr_user_id);
        $final_singles = $this->get_user_final_singles($curr_user_id);
        
        ob_start(); ?>
<div class="container-flex" style="margin-bottom:50px;">
    <div class="row justify-content-md-center">
        <div class="col-sm-5 foos-menu-selector" id="inp_btn" style="background-color:lightgray;"><h1 style="margin-top:10px;">In Progress</h1></div>
        <div class="col-sm-5 foos-menu-selector" id="final_btn"><h1 style="margin-top:10px;">Final</h1></div>
    </div>
</div>
    
    <!-- New match row -->
    <div class="row justify-content-md-center foos-match-row">
        <div class="col-sm-5">
            <div class="row">
                <div class="col-"><?php echo get_avatar($curr_user->ID, 80) ?></div>
                <div class="col">
                    <div class="row">
                        <div class="col"><h3><?php echo $curr_user->display_name ?></h3></div>
                    </div>
                    <div class="row">
                        <div class="col"><?php echo get_user_meta(curr_user->ID, 'foos_elo', true) ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-2 foos-score-box-form" style="text-align:center;">
            <form method="post">
                <input type="hidden" name="p1_id" value="<?php echo $curr_user->ID ?>">
                <input type="hidden" name="p2_id" value="-1">
                <h3>
                    <input type="text" name="p1_score" value="0" class="foos-score-box" autocomplete="off">
                    -
                    <input type="text" name="p2_score" value="0" class="foos-score-box" autocomplete="off">
                </h3>
                <button type="submit" class="btn btn-dark" disabled>Start</button>
            </form>
        </div>
        <div class="col-sm-5" style="text-align:right;">
            <div class="row">
                <div class="col">
                    <div class="row">
                        <div class="col"><h3></h3></div>
                    </div>
                    <div class="row">
                        <div class="col"><?php echo get_user_meta($p2_id, 'foos_elo', true) ?></div>
                    </div>
                    <div class="row">
                        <div class="col">Chance: <?php echo $p2_chance ?>%</div>
                    </div>
                </div>
                <div class="col-"><?php echo get_avatar($p2_id, 80) ?></div>
            </div>
        </div>
    </div>
</div>

        <?php
        return ob_get_clean();
    }
}
