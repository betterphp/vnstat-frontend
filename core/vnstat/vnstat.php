<?php

declare(strict_types=1);

namespace betterphp\vnstat_frontend\vnstat;

use betterphp\vnstat_frontend\network\network_interface;

class vnstat {

	private $interface;

	/**
	 * @param network_interface $interface The interface to work with
	 */
	public function __construct(network_interface $interface) {
		// No need to check if this is a valid name since the constructor
		// for network_interface will have done that already.
		$this->interface = $interface;
	}

	/**
	 * Gets traffic data from the database.
	 *
	 * @return array An array of information.
	 */
	public function get_traffic(): array {
		$data = explode("\n", trim(shell_exec('vnstat --dumpdb -i ' . escapeshellarg($this->interface->get_name()))));

		$traffic = array();

		$traffic['days'] = array();
		$traffic['months'] = array();
		$traffic['top'] = array();
		$traffic['hours'] = array();

		foreach ($data as $line) {
			$parts = explode(';', $line);

			switch ($parts[0]) {
				case 'd';
					if ($parts[2] != 0) {
						$time = intval($parts[2]);

						$traffic['days'][$time]['rx'] = (floatval($parts[3]) * 1024 + floatval($parts[5]));
						$traffic['days'][$time]['tx'] = (floatval($parts[4]) * 1024 + floatval($parts[6]));
					}
				break;
				case 'm':
					if ($parts[2] != 0) {
						$time = intval($parts[2]);

						$traffic['months'][$time]['rx'] = (floatval($parts[3]) * 1024 + floatval($parts[5]));
						$traffic['months'][$time]['tx'] = (floatval($parts[4]) * 1024 + floatval($parts[6]));
					}
				break;
				case 't';
					if ($parts[2] != 0) {
						$time = intval($parts[2]);

						$traffic['top'][$time]['rx'] = (floatval($parts[3]) * 1024 + floatval($parts[5]));
						$traffic['top'][$time]['tx'] = (floatval($parts[4]) * 1024 + floatval($parts[6]));
					}
				break;
				case 'h':
					if ($parts[2] != 0) {
						$time = intval($parts[2]);

						$traffic['hours'][$time]['rx'] = floatval($parts[3]);
						$traffic['hours'][$time]['tx'] = floatval($parts[4]);
					}
				break;
				default:
					if (count($parts) == 2) {
						$traffic[$parts[0]] = $parts[1];
					}
			}
		}

		ksort($traffic['days']);
		ksort($traffic['months']);
		ksort($traffic['hours']);

		return $traffic;
	}

	/**
	 * Gets the live rates for an interface.
	 *
	 * Note that this method blocks for 2 seconds while sampling.
	 *
	 * @return array An array of rate information.
	 */
	public function get_live_traffic(): array {
		$data = explode("\n", trim(shell_exec('vnstat -tr 2 -ru 0 -i ' . escapeshellarg($this->interface->get_name()))));
		$traffic = array();

		foreach (array_slice($data, -2) as $line) {
			$parts = preg_split('#\s+#', trim($line));

			$rate = floatval($parts[1]);

			switch ($parts[2]) {
				case 'MiB/s':
					$rate *= 1024;
				break;
				case 'GiB/s':
					$rate *= 1048576;
				break;
			}

			$traffic[$parts[0]]['rate'] = $rate;
			$traffic[$parts[0]]['packets'] = intval($parts[3]);
		}

		return $traffic;
	}

}
