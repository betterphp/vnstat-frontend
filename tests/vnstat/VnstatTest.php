<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use betterphp\vnstat_frontend\network\network_interface;
use betterphp\vnstat_frontend\vnstat\vnstat;

class VnstatTest extends TestCase {

	private function getTestVnstatInstance(): vnstat {
		return network_interface::get_all()[0]->get_vnstat();
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
