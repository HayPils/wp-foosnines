(function( $ ) {
    'use strict';

    $('.foos-score-box-form input').on('keyup', function() {
        var submit_btn = jQuery(this).parent().parent().children('button');
        submit_btn.removeClass('btn-dark');
        submit_btn.removeClass('btn-success');
        submit_btn.addClass('btn-primary');
        submit_btn.text('Submit');
    });
    $('.foos-score-box-form input').click(function() {this.select();});

    $('#inp_btn').click(function() {
        $(this).css('background-color', 'lightgray');
        $('#final_btn').css('background-color', '');
        $('#inp_matches').show();
        $('#new_match_row').show();
        $('#final_matches').hide();
    });
    $('#final_btn').click(function() {
        $(this).css('background-color', 'lightgray');
        $('#inp_btn').css('background-color', '');
        $('#inp_matches').hide();
        $('#new_match_row').hide();
        $('#final_matches').show();
    });
    
    $.typeahead({
        input: '.player-typeahead',
        order: 'asc',
        source: {
            players: {
                ajax: {
                    url: ajax_object.ajaxurl,
                    data: {
                        action: 'get_player_names'
                    }
                }
            }
        },
        hint: true,
        generateOnLoad: true,
        mustSelectItem: true,
        callback: {
            onClickAfter: function(node, a, item, event) {
                load_opponent(item.id);
                $('#new_p2id').val(item.id);
            },
            onSubmit: function(node, form, item, event) { // prevent form submission on enter
                event.preventDefault();
                load_opponent(item.id);
                $('#new_p2id').val(item.id);
            }
        }   
    });
    
    function load_opponent(player_id) {
        $.ajax({
            url: ajax_object.ajaxurl,
            data: {
                action: 'get_player_info',
                player_id: player_id
            },
            success: function(response) {
                response = JSON.parse(response);
                $('#new_p2_rating').text(response.rating);
                $('#new_p2_avatar').html(response.avatar);
            }
        });
    }
    
    
})( jQuery );

