(function( $ ) {
    'use strict';
    $('.foos-leaderboard-row').click(function() {
        var route = '/player-info/?player_id=' + $(this).attr('data-player-id');
        window.location.assign(window.location.hostname + route);
    });

})( jQuery );


