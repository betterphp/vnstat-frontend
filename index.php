<?php

declare(strict_types=1);

include('core/init.inc.php');

$interfaces = vnstat::get_interfaces();

if (empty($interfaces)) {
	die('No network interfaces detected.');
}

$selected_interface = (isset($_GET['interface']) && in_array($_GET['interface'], $interfaces)) ? $_GET['interface'] : $interfaces[0];

$traffic = vnstat::get_traffic($selected_interface);

?>
<!DOCTYPE html>
<html>
	<head>
		<title>Traffic Stats for <?php echo $selected_interface; ?></title>
		<meta charset="utf-8" />
		<link rel="stylesheet" type="text/css" media="all" href="ext/css/build/style.min.css" />
		<script type="text/javascript">
			var selectedInterface = '<?php echo $selected_interface; ?>';
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

						foreach ($interfaces as $interface) {
							echo '<option', ($interface == $selected_interface) ? ' selected="selected"' : '', '>', $interface, '</option>';
						}

						?>
					</select>
					<input type="submit" value="Update" />
				</div>
			</form>
			<table>
				<tbody>
					<tr><td>In</td><td class="numeric-cell">0.00 MiB/s</td><td class="numeric-cell">0 packets/s</td></tr>
					<tr><td>Out</td><td class="numeric-cell">0.00 MiB/s</td><td class="numeric-cell">0 packets/s</td></tr>
				</tbody>
			</table>
		</div>

		<div class="main-content">
			<?php

			$entries = array(
				array('key' => 'hours', 'format' => 'H:00'),
				array('key' => 'days', 'format' => 'l jS F'),
				array('key' => 'months', 'format' => 'F Y'),
			);

			foreach ($entries as $entry) {
				if (empty($traffic[$entry['key']])) {
					continue;
				}

				$name = substr($entry['key'], 0, -1);

				$chart_data = array();
				$chart_data['cols'][] = array('id' => 'hour', 'label' => ucfirst($name), 'type' => 'datetime');
				$chart_data['cols'][] = array('id' => 'rx', 'label' => 'Received', 'type' => 'number');
				$chart_data['cols'][] = array('id' => 'tx', 'label' => 'Sent', 'type' => 'number');

				foreach ($traffic[$entry['key']] as $time => $data) {
					$chart_data['rows'][] = [
						'c' => [
							['v' => 'Date(' . ($time * 1000) . ')', 'f' => date($entry['format'], $time)],
							['v' => round(($data['rx'] / 1024)), 'f' => number_format(round(($data['rx'] / 1024))) . ' MiB'],
							['v' => round(($data['tx'] / 1024)), 'f' => number_format(round(($data['tx'] / 1024))) . ' MiB'],
						]
					];
				}

				?>
				<div class="chart-wrapper">
					<div class="chart" id="<?php echo $entry['key']; ?>-chart" data-chart-data="<?php echo htmlentities(json_encode($chart_data)); ?>"></div>
				</div>
				<?php
			}

			?>
		</div>
	</body>
</html>
