(function( $ ) {
	'use strict';

        google.charts.setOnLoadCallback(drawOnFireGauge);
        
        function drawOnFireGauge() {         
            const gauge_max = 20;
            var on_fire_cnt = parseInt($('#on_fire_gauge').attr('data-fire-cnt'));
            var penult_lws = parseInt($('#on_fire_gauge').attr('data-p-lws'));
            var lws = parseInt($('#on_fire_gauge').attr('data-lws'));
            // Define the chart to be drawn.

            var data = google.visualization.arrayToDataTable([
                ['Label', 'Win Streak'],
                ['Streak', on_fire_cnt]
            ]);

            var red_yellow_grad = (on_fire_cnt < lws) ? lws : penult_lws;

            var options = {
                width: 120, height: 120,
                greenFrom: 0, greenTo: 3,
                yellowFrom: 3, yellowTo: red_yellow_grad,
                redFrom: red_yellow_grad, redTo: gauge_max,
                minorTicks: 5,
                max: gauge_max,
            };

            // Instantiate and draw the chart.
            var chart = new google.visualization.Gauge(document.getElementById('on_fire_gauge'));

            chart.draw(data, options);

        }

})( jQuery );


