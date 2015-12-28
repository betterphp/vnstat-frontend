<?php

include('core/init.inc.php');

$interfaces = vnstat::get_interfaces();

if (empty($interfaces)){
	die('No network interfaces detected.');
}

$selected_interface = (isset($_GET['interface']) && in_array($_GET['interface'], $interfaces)) ? $_GET['interface'] : $interfaces[0];

$traffic = vnstat::get_traffic($selected_interface);

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<link rel="stylesheet" type="text/css" media="all" href="ext/css/main.css" />
		<script type="text/javascript">
			var selectedInterface = '<?php echo $selected_interface; ?>';
		</script>
		<script type="text/javascript" src="https://www.google.com/jsapi"></script>
		<script type="text/javascript" src="ext/jsc/http.lib.js"></script>
		<script type="text/javascript" src="ext/jsc/main.js"></script>
		<title>Traffic Stats for <?php echo $selected_interface; ?></title>
	</head>
	<body>
		<div id="main-header">
			<form action="" method="get">
				<div>
					<select name="interface">
						<?php

						foreach ($interfaces as $interface){
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

		<div id="content-wrapper">
			<?php

			$entries = array(
				array('key' => 'hours', 'format' => 'H:00'),
				array('key' => 'days', 'format' => 'l jS F'),
				array('key' => 'months', 'format' => 'F Y'),
			);

			foreach ($entries as $entry){
				if (empty($traffic[$entry['key']])){
					continue;
				}

				$name = substr($entry['key'], 0, -1);

				$chart_data = array();
				$chart_data['cols'][] = array('id' => 'hour', 'label' => ucfirst($name), 'type' => 'datetime');
				$chart_data['cols'][] = array('id' => 'rx', 'label' => 'Received', 'type' => 'number');
				$chart_data['cols'][] = array('id' => 'tx', 'label' => 'Sent', 'type' => 'number');

				foreach ($traffic[$entry['key']] as $time => $data){
					$chart_data['rows'][] = array('c' => array(
						array('v' => 'Date(' . ($time * 1000) . ')', 'f' => date($entry['format'], $time)),
						array('v' => round($data['rx'] / 1024), 'f' => number_format(round($data['rx'] / 1024)) . ' MiB'),
						array('v' => round($data['tx'] / 1024), 'f' => number_format(round($data['tx'] / 1024)) . ' MiB'),
					));
				}

				?>
				<div class="content-box">
					<div class="head"><h1><?php echo ucfirst($entry['key']); ?></h1></div>
					<div class="body">
						<div class="chart" id="<?php echo $entry['key']; ?>-chart"></div>
						<script type="text/javascript">var <?php echo $entry['key']; ?>Data = <?php echo json_encode($chart_data); ?>;</script>
					</div>
				</div>
				<?php
			}

			?>
		</div>
	</body>
</html>
