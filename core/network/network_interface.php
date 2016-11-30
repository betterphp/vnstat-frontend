<?php

declare(strict_types=1);

namespace betterphp\vnstat_frontend\network;

use betterphp\vnstat_frontend\vnstat\vnstat;

class network_interface {

	private $name;

	/**
	 * @param string $name The name of the network interface, e.g. eth0
	 */
	public function __construct(string $name) {
		$valid_interfaces = self::get_all_names();

		if (!in_array($name, $valid_interfaces)) {
			throw new \Exception('Invalid interface name');
		}

		$this->name = $name;
	}

	/**
	 * Gets the name of the network interface
	 *
	 * @return string the name
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Gets a vnstat instance to work with this interface
	 *
	 * @return vnstat The instance
	 */
	public function get_vnstat(): vnstat {
		return new vnstat($this);
	}

	/**
	 * Gets a list of network interface names monitored by vnstat
	 *
	 * @return array A list of names
	 */
	private static function get_all_names(): array {
		static $names = null;

		if ($names === null) {
			// How often are we going to add a new nic while this script is running?
			$files = scandir('/var/lib/vnstat');

			$names = array_filter($files, function ($file) {
				return substr($file, 0, 1) !== '.';
			});

			$names = array_values($names);
		}

		return $names;
	}

	/**
	 * Gets a list of available interfaces.
	 *
	 * @return array A list of available interfaces.
	 */
	public static function get_all(): array {
		return array_map(function ($name) {
			return new network_interface($name);
		}, self::get_all_names());
	}

}
