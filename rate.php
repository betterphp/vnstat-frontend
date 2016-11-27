<?php

declare(strict_types=1);

namespace betterphp\vnstat_frontend;

use betterphp\vnstat_frontend\vnstat\vnstat;

include('core/init.inc.php');

header('Content-Type: application/json');

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$interfaces = vnstat::get_interfaces();
$selected_interface = (isset($_GET['interface']) && in_array($_GET['interface'], $interfaces))
							? $_GET['interface']
							: $interfaces[0];

echo json_encode(vnstat::get_live_traffic($selected_interface));
