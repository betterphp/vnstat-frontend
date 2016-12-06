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
        <script type="text/javascript" src="ext/jsc/build/script.min.js"></script>
    </head>
    <body>
        <div class="main-header">
            <nav>
                <?php

                foreach ($interfaces as $option) {
                    $class_name = ($option === $interface) ? 'current' : '';

                    ?>
                    <a href="?interface=<?= $option->get_name(); ?>" class="<?= $class_name; ?>">
                        <?= $option->get_name(); ?>
                    </a>
                    <?php
                }

                ?>
            </nav>
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

                foreach ($vnstat->$method_name() as $entry) {
                    // Convert to ms for JavaScript Date()
                    $start_time = ($entry->get_start()->getTimestamp() * 1000);
                    $end_time = ($entry->get_end()->getTimestamp() * 1000);

                    $chart_data[] = [
                        // Adverage of start and end times used as the rate reported is averaged over this time
                        'time' => ($start_time + (($end_time - $start_time) / 2)),
                        'sent' => $entry->get_sent()->get_byte_rate(),
                        'received' => $entry->get_received()->get_byte_rate(),
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
