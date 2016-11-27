<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use betterphp\vnstat_frontend\vnstat\vnstat;

class VnstatTest extends TestCase {

	public function testListInterfaces() {
		$interfaces = vnstat::get_interfaces();

		$this->assertNotEmpty($interfaces);
	}

}
