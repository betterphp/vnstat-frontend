<?php

declare(strict_types=1);

namespace betterphp\vnstat_frontend;

use betterphp\vnstat_frontend\network\network_interface;
use betterphp\vnstat_frontend\vnstat\vnstat;

include('core/init.inc.php');

$interfaces = network_interface::get_all();

if (empty($interfaces)) {
	die('No network interfaces detected.');
}

if (isset($_GET['interface'])) {
	// Look for the selected interface if there is one
	foreach ($interfaces as $test) {
		if ($test->get_name() === $_GET['interface']) {
			$interface = $test;
			break;
		}
	}
}

// Fall back to the first one on the list
if (!isset($interface)) {
	$interface = $interfaces[0];
}

$vnstat = $interface->get_vnstat();

?>
<!DOCTYPE html>
<html>
	<head>
		<title>Traffic Stats for <?php echo $interface->get_name(); ?></title>
		<meta charset="utf-8" />
		<link rel="stylesheet" type="text/css" media="all" href="ext/css/build/style.min.css" />
		<script type="text/javascript">
			var selectedInterface = '<?php echo $interface->get_name(); ?>';
		</script>
		<script type="text/javascript" src="https://www.google.com/jsapi"></script>
		<script type="text/javascript" src="ext/jsc/build/script.min.js"></script>
	</head>
	<body>
		<div class="main-header">
			<form action="" method="get">
				<div>
					<select name="interface">
						<?php

						foreach ($interfaces as $option) {
							$selected = ($option === $interface) ? ' selected="selected"' : '';

							echo '<option ', $selected, '>', $option->get_name(), '</option>';
						}

						?>
					</select>
					<input type="submit" value="Update" />
				</div>
			</form>
			<table>
				<tbody>
					<tr>
						<td>In</td>
						<td class="numeric-cell">0.00 MiB/s</td>
						<td class="numeric-cell">0 packets/s</td>
					</tr>
					<tr>
						<td>Out</td>
						<td class="numeric-cell">0.00 MiB/s</td>
						<td class="numeric-cell">0 packets/s</td>
					</tr>
				</tbody>
			</table>
		</div>

		<div class="main-content">
			<?php

			$daily = $vnstat->get_daily_traffic();
			$monthly = $vnstat->get_monthly_traffic();

			$charts = [
				['get_hourly_traffic', 'hour', 'Hour', 'H:00'],
				['get_daily_traffic', 'day', 'Day', 'l jS F'],
				['get_monthly_traffic', 'month', 'Month', 'F Y'],
			];

			foreach ($charts as $chart_spec) {
				list($method_name, $id, $label, $date_format) = $chart_spec;

				$chart_data = [];
				$chart_data['cols'][] = ['id' => $id, 'label' => $label, 'type' => 'datetime'];
				$chart_data['cols'][] = ['id' => 'rx', 'label' => 'Received', 'type' => 'number'];
				$chart_data['cols'][] = ['id' => 'tx', 'label' => 'Sent', 'type' => 'number'];

				foreach ($vnstat->$method_name() as $entry) {
					$mbps_received = ($entry->get_received()->get_byte_rate() / 1024 / 1024);
					$mbps_sent = ($entry->get_sent()->get_byte_rate() / 1024 / 1024);

					$chart_data['rows'][] = [
						'c' => [
							[
								'v' => 'Date(' . ($entry->get_start()->getTimestamp() * 1000) . ')',
								'f' => $entry->get_start()->format($date_format),
							],
							[
								'v' => $mbps_received,
								'f' => number_format($mbps_received) . ' MiB',
							],
							[
								'v' => $mbps_sent,
								'f' => number_format($mbps_sent) . ' MiB',
							],
						]
					];
				}

				?>
				<div class="chart-wrapper">
					<div class="chart" data-chart-data="<?php echo htmlentities(json_encode($chart_data)); ?>"></div>
				</div>
				<?php
			}

			?>
		</div>
	</body>
</html>
