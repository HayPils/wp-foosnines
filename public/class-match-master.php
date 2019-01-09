<?php

/**
 * A class to handle match selecting, updating and histories
 *
 * @author Hayden Pilsner
 */
class Match_Master {
    public function __construct() {}
    
    public function create_singles_match($p1_id, $p2_id) {      
        $curr_user_id = get_current_user_id();
        // check if player ids exist
        // prevent other users from creating matches for other players
        // and from creating matches with themselves
        if (!$this->user_id_exists($p1_id) || !$this->user_id_exists($p2_id)
            || ($curr_user_id != $p1_id && $curr_user_id != $p2_id)
            || ($curr_user_id == $p1_id && $curr_user_id == $p2_id)) {
            return;
        }
        // check if match already exists
        $args = array(
            'post_type' => 'singles_match',
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'relation' => 'AND',
                    array(
                        'key' => 'p1_id',
                        'value' => $p1_id,
                        'compare' => '=',
                    ),
                    array(
                        'key' => 'p2_id',
                        'value' => $p2_id,
                        'compare' => '='
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
                        'key' => 'p1_id',
                        'value' => $p2_id,
                        'compare' => '=',
                    ),
                    array(
                        'key' => 'p2_id',
                        'value' => $p1_id,
                        'compare' => '='
                    ),
                    array(
                        'key' => 'is_final',
                        'value' => 0,
                        'compare' => '='
                    )
                )
            )
        );
        $wp_query = new WP_Query($args);
        $existing_match_ids = $wp_query->get_posts();

        if (count($existing_match_ids) > 0) return;  // do not create new match if one already exists

        $p1_user = get_userdata($p1_id);
        $p2_user = get_userdata($p2_id);
        $p1_name = $p1_user->first_name . ' ' . $p1_user->last_name;
        $p2_name = $p2_user->first_name . ' ' . $p2_user->last_name;
        
        // create match post
        wp_insert_post(array(
            'post_type' => 'singles_match',
            'post_title' => $p1_name . ' vs. ' . $p2_name,
            'post_status' => 'publish',
            'meta_input' => array(
                'p1_id' => $p1_id,
                'p2_id' => $p2_id,
                'p1_score'  => 0,
                'p2_score'  => 0,
                'is_final' => 0,
                'p1_accept' => 0,
                'p2_accept' => 0
            )
        ));
    }
    
    public function get_all_final_singles($order='DESC') {
        $singles_ids = new WP_Query([
            'post_type' => 'singles_match',
            'posts_per_page'    => -1,
            'fields'    => 'ids',
            'order'     => $order,
            'meta_key'  => 'final_date',
            'orderby'   => 'meta_value_num',
            'meta_query' => array(
                array(
                    'key' => 'is_final',
                    'value' => 1,
                    'compare' => '='
                )
            )
        ]);
        return $singles_ids->posts;
    }
    
    public function user_id_exists($user_id){
        global $wpdb;

        $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->users WHERE ID = %d", $user_id));

        if($count == 1){ return TRUE; }else{ return FALSE; }
    }
    
}
