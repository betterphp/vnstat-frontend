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

		$bandwidth = new bandwidth($bytes, $packets, $start, $end);

		$this->assertSame($bytes, $bandwidth->get_byte_rate());
		$this->assertSame($packets, $bandwidth->get_packet_rate());
		$this->assertSame($start, $bandwidth->get_start());
		$this->assertSame($end, $bandwidth->get_end());
	}

	public function testEndBeforeStartTime() {
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('End time not after start time');

		$start = DateTime::createFromFormat('U', (string) mktime(1, 30, 0));
		$end = DateTime::createFromFormat('U', (string) mktime(1, 0, 0));

		new bandwidth(0, 0, $start, $end);
	}

}
