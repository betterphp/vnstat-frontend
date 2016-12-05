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
    die('Could not find network interface.');
}

$vnstat = $interface->get_vnstat();

header('Content-Type: application/json');

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$traffic = $vnstat->sample(2);

echo json_encode([
    'sent' => [
        'bytes' => $traffic->get_sent()->get_byte_rate(),
        'packets' => $traffic->get_sent()->get_packet_rate(),
    ],
    'received' => [
        'bytes' => $traffic->get_received()->get_byte_rate(),
        'packets' => $traffic->get_received()->get_packet_rate(),
    ],
]);
