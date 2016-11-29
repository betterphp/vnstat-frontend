<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use betterphp\vnstat_frontend\data\bandwidth;

class BandwidthTest extends TestCase {

	public function testGetProperties() {
		$bytes = 1000;
		$packets = 100000;
		$start = DateTime::createFromFormat('U', (string) mktime(0, 0, 0));
		$end = DateTime::createFromFormat('U', (string) mktime(1, 0, 0));

		$bandwith = new bandwidth($bytes, $packets, $start, $end);

		$this->assertSame($bytes, $bandwith->get_byte_rate());
		$this->assertSame($packets, $bandwith->get_packet_rate());
		$this->assertSame($start, $bandwith->get_start());
		$this->assertSame($end, $bandwith->get_end());
	}

}
