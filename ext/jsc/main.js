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

	google.load('visualization', '1.1', { packages: ['corechart'] });

	google.setOnLoadCallback(function(){
		var options = {
			focusTarget: 'category',
			chartArea: { width: '90%', height: '85%', left: '5%', top: '5%' },
			legend: { position: 'none' },
			hAxis: { baselineColor: 'none' },
			vAxis: { baselineColor: 'none', format: '' }
		};

		var containers = document.querySelectorAll('.chart[data-chart-data]');

		Array.prototype.forEach.call(containers, function(container){
			var data = JSON.parse(container.getAttribute('data-chart-data'));
			var dataTable = new google.visualization.DataTable(data);

			(new google.visualization.AreaChart(container)).draw(dataTable, options);
		});
	});
})();
