<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use betterphp\vnstat_frontend\vnstat\vnstat;

class VnstatTest extends TestCase {

	public function testListInterfaces() {
		$interfaces = vnstat::get_interfaces();

		$this->assertInternalType('array', $interfaces);
		$this->assertNotEmpty($interfaces);
	}

	private function getTestVnstatInstance(): vnstat {
		$interfaces = vnstat::get_interfaces();

		return new vnstat($interfaces[0]);
	}

	// These are essentially placeholder tests until the return values are refactored.
	// Just call the methods and make sure they return an array for now.

	public function testGetTraffic() {
		$vnstat = $this->getTestVnstatInstance();

		$traffic = $vnstat->get_traffic();

		$this->assertInternalType('array', $traffic);
	}

	public function testGetLiveTraffic() {
		$vnstat = $this->getTestVnstatInstance();

		$live_traffic = $vnstat->get_live_traffic();

		$this->assertInternalType('array', $live_traffic);
	}

	//

}
