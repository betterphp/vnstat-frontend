<?php

declare(strict_types=1);

namespace betterphp\vnstat_frontend\vnstat;

use betterphp\vnstat_frontend\data\traffic;
use betterphp\vnstat_frontend\data\bandwidth;
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
	 * Used to parse the text output from vnstat live sample command
	 *
	 * @param string $line The output line
	 * @param \DateTime $start The time that the measurement started
	 * @param \DateTime $end The time that the measurement completed
	 *
	 * @return bandwidth The resulting bandwidth measurement
	 */
	private function parse_live_sample_line(string $line, \DateTime $start, \DateTime $end): bandwidth {
		$parts = preg_split('#\s+#', trim($line));
		$rate = floatval($parts[1]);
		$packets = intval($parts[3]);

		// The minimum unit vnstat reports is KiB/s
		$rate *= 1024;

		switch ($parts[2]) {
			case 'MiB/s':
				$rate *= 1024;
			break;
			case 'GiB/s':
				$rate *= (1024 * 1024);
			break;
		}

		return new bandwidth((int) $rate, $packets, $start, $end);
	}

	/**
	 * Samples the traffic on the interface for a number of seconds and takes an average
	 *
	 * @param integer $duration How long to take the measurement over
	 *
	 * @return traffic The resulting measurement.
	 */
	public function sample(int $duration): traffic {
		if ($duration < 2) {
			throw new \Exception('Sample duration below 2 seconds');
		}

		$cmd_arg_time = escapeshellarg((string) $duration);
		$cmd_arg_ifname = escapeshellarg($this->interface->get_name());

		$start = new \DateTime();
		$command_output = shell_exec("vnstat -tr {$cmd_arg_time} -ru 0 -i {$cmd_arg_ifname}");
		$end = new \DateTime();

		$data = explode("\n", trim($command_output));

		if (count($data) !== 5) {
			throw new \Exception('vnstat executable returned an error: ' . end($data));
		}

		$sent_line = $data[4];
		$received_line = $data[3];

		$sent = $this->parse_live_sample_line($sent_line, $start, $end);
		$received = $this->parse_live_sample_line($received_line, $start, $end);

		return new traffic($sent, $received);
	}

}
