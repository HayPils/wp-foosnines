(function( $ ) {
    'use strict';

    $('.foos-score-box-form input').change(function() {
        var submit_btn = jQuery('.foos-score-box-form button');
        submit_btn.removeClass('btn-success');
        submit_btn.addClass('btn-primary');
        submit_btn.text('Submit');
    });
    $('.foos-score-box-form input').click(function() {this.select();});

    $('#inp_btn').click(function() {
        $(this).css('background-color', 'lightgray');
        $('#final_btn').css('background-color', '');
        $('#inp_matches').show();
        $('#final_matches').hide();
    });
    $('#final_btn').click(function() {
        $(this).css('background-color', 'lightgray');
        $('#inp_btn').css('background-color', '');
        $('#inp_matches').hide();
        $('#final_matches').show();
    });
    
    
})( jQuery );

