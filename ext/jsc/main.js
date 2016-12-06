(function(){
	"use strict";

	window.addEventListener('DOMContentLoaded', function(event){
		(function updateRates(){
			var request = new XMLHttpRequest();

			request.addEventListener('readystatechange', function(event){
				if (this.readyState !== 4 || this.status !== 200) {
					return;
				}

				var data = JSON.parse(this.responseText);
				var table = document.querySelector('.main-header > table');
				var cells = table.getElementsByClassName('numeric-cell');

				for (var c = 0; c < cells.length; ++c){
					while (cells[c].hasChildNodes()){
						cells[c].removeChild(cells[c].lastChild);
					}
				}

				cells[0].appendChild(document.createTextNode((data.received.bytes / 1024 / 1024).toFixed(2) + ' MiB/s'));
				cells[1].appendChild(document.createTextNode(data.received.packets + ' packets/s'));

				cells[2].appendChild(document.createTextNode((data.sent.bytes / 1024 / 1024).toFixed(2) + ' MiB/s'));
				cells[3].appendChild(document.createTextNode(data.sent.packets + ' packets/s'));

				updateRates();
			});

			request.open('GET', 'rate.php?interface=' + selectedInterface);
			request.send();
		})();
	});

	window.addEventListener('DOMContentLoaded', function(event){
		var charts = document.querySelectorAll('[data-chart-data]');

		for (var i = 0; i < charts.length; ++i) {
			var chart = charts[i];
			var data = JSON.parse(chart.dataset.chartData);

			c3.generate({
				bindto: chart,
				data: {
					json: data,
					type: 'area',
					keys: {
						x: 'time',
						value: [
							'received',
							'sent',
						],
					},
					names: {
						received: 'Received',
						sent: 'Sent',
					},
				},
				axis: {
					x: {
						type: 'timeseries',
					},
				},
			});
		}
	});

})();
