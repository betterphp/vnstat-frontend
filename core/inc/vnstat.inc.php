<?php

declare(strict_types=1);

/**
 * An interface to the vnstat executable
 */
class vnstat {

	/**
	 * Gets a list of available interfaces.
	 *
	 * @param array $ignore A list of interfaces to ignore, defaults to lo only.
	 * @return array A list of available interfaces.
	 */
	public static function get_interfaces(array $ignore = ['lo']): array {
		$data = shell_exec('ifconfig -a');
		preg_match_all('#^([a-z0-9]+): flags#Uim', $data, $matches);
		$names = $matches[1];

		foreach ($names as $key => $name) {
			if (in_array($name, $ignore)) {
				unset($names[$key]);
			}
		}

		return $names;
	}

	/**
	 * Gets traffic data from the database.
	 *
	 * @param string $interface The name of the interface to fetch data for.
	 * @return array An array of information.
	 */
	public static function get_traffic(string $interface): array {
		$data = explode("\n", trim(shell_exec('vnstat --dumpdb -i ' . escapeshellarg($interface))));

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
	 * @param string $interface The name of the interface.
	 * @return array An array of rate information.
	 */
	public static function get_live_traffic(string $interface): array {
		$data = explode("\n", trim(shell_exec('vnstat -tr 2 -ru 0 -i ' . escapeshellarg($interface))));
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
