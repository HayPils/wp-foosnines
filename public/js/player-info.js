(function( $ ) {
    'use strict';

    google.charts.setOnLoadCallback(drawEloHistoryGraph);

    function drawEloHistoryGraph() {
        const urlParams = new URLSearchParams(window.location.search);
        const player_id = urlParams.get('player_id');
        var data = new google.visualization.DataTable();

        data.addColumn('number', 'Game');
        data.addColumn('number', 'Rating');

        $.ajax({
            url: ajax_object.ajaxurl,
            data: {
                action: 'get_elo_history',
                player_id: player_id // CHANGE
            },
            success: (response) => {
                var elo_history = $.parseJSON(response);
                data.addRows(elo_history.length);
                for (var i = 0; i < elo_history.length; i++) {
                    data.setCell(i, 0, i);
                    data.setCell(i, 1, elo_history[i]);
                }
                
                var options = {
                    chart: {
                      title: 'Rating History',
                    },
                    width: 600,
                    height: 400
                };

                var chart = new google.charts.Line(document.getElementById('elo_history_graph'));

                chart.draw(data, google.charts.Line.convertOptions(options));
            }
        });

    }

})( jQuery );

