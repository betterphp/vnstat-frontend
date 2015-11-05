window.addEventListener('DOMContentLoaded', function(event){
	(function updateRates(){
		http.get({
			url: 'rate.php?interface=' + selectedInterface,
			timeout: 5000,
			onSuccess: function(response){
				var data = JSON.parse(response);
				var table = document.querySelector('#main-header > table');
				var cells = table.getElementsByClassName('numeric-cell');

				for (var c = 0; c < cells.length; ++c){
					while (cells[c].hasChildNodes()){
						cells[c].removeChild(cells[c].lastChild);
					}
				}

				cells[0].appendChild(document.createTextNode((data.rx.rate / 1024).toFixed(2) + ' MiB/s'));
				cells[1].appendChild(document.createTextNode(data.rx.packets + ' packets/s'));

				cells[2].appendChild(document.createTextNode((data.tx.rate / 1024).toFixed(2) + ' MiB/s'));
				cells[3].appendChild(document.createTextNode(data.tx.packets + ' packets/s'));

				updateRates();
			}
		});
	})();
}, false);

google.load('visualization', '1.1', { packages: ['corechart'] });

google.setOnLoadCallback(function(){
	var options = {
		focusTarget: 'category',
		chartArea: { width: '90%', height: '85%', left: '5%', top: '5%' },
		legend: { position: 'none' },
		hAxis: { baselineColor: 'none' },
		vAxis: { baselineColor: 'none', format: '' }
	};

	if (hoursData){
		var hoursDataTable = new google.visualization.DataTable(hoursData);
		var hoursChart = new google.visualization.AreaChart(document.getElementById('hours-chart'));

		hoursChart.draw(hoursDataTable, options);
	}

	if (daysData){
		var daysDataTable = new google.visualization.DataTable(daysData);
		var daysChart = new google.visualization.AreaChart(document.getElementById('days-chart'));

		daysChart.draw(daysDataTable, options);
	}

	if (monthsData){
		var monthsDataTable = new google.visualization.DataTable(monthsData);
		var monthsChart = new google.visualization.AreaChart(document.getElementById('months-chart'));

		monthsChart.draw(monthsDataTable, options);
	}
});
