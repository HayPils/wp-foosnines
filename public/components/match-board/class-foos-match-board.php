<?php

/**
 * This class handles preprocessing the match board component for the public 
 * match board page
 *
 * @author Hayden Pilsner
 */
class Foos_Match_Board {
    
    public function singles_list() {
        // get all singles matches
        $match_master = new Match_Master();
        $singles_ids = $match_master->get_all_final_singles();
        $elo_master = new Elo_Master();
        ?>
<div class="container-flex" style="margin-bottom:50px;">
    <div class="row justify-content-md-center">
        <div class="col-sm-5 foos-menu-selector" style="background-color:lightgray;"><h1 style="margin-top:10px;">Singles</h1></div>
        <div class="col-sm-5 foos-menu-selector"><h1 style="margin-top:10px;">Doubles</h1></div>
    </div>
</div>

<!-- singles matches -->
<div class="container-flex">
        <?php
        foreach ($singles_ids as $match_id) :
            $p1_id = get_post_meta($match_id, 'p1_id', true);
            $p2_id = get_post_meta($match_id, 'p2_id', true);
            $p1_user = get_userdata($p1_id);
            $p2_user = get_userdata($p2_id);
            $p1_name = $p1_user->display_name;
            $p2_name = $p2_user->display_name;
            ?>
    <div class="row justify-content-md-center foos-match-row">
        <div class="col-sm-4">
            <div class="row">
                <div class="col-"><?php echo get_avatar($p1_id, 80) ?></div>
                <div class="col"><h3><?php echo $p1_name ?></h3></div>
            </div>
        </div>
        <div class="col-sm-2" style="text-align:center;">
            <?php echo Foos_Info_Filter::foos_score_display($match_id) ?>
            <p><?php echo Foos_Info_Filter::foos_date(intval(get_post_meta($match_id, 'final_date', true))) ?></p>
        </div>
        <div class="col-sm-4" style="text-align:right;">
            <div class="row">
                <div class="col"><h3><?php echo $p2_name ?></h3></div>
                <div class="col-"><?php echo get_avatar($p2_id, 80) ?></div>
            </div>
        </div>
    </div>
            <?php
        endforeach; ?>
</div>
        <?php
    }
    
}
