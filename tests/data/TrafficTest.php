<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use betterphp\vnstat_frontend\data\bandwidth;
use betterphp\vnstat_frontend\data\traffic;

class TrafficTest extends TestCase {

	private $start;
	private $received;
	private $traffic;

	private function createMockBandwidthObject() {
		// Create a mock bandwidth object - a bit overkil maybe but want to only test the traffic class here
		$mock = $this
			->getMockBuilder(bandwidth::class)
			->disableOriginalConstructor()
			->getMock();

		$mock->method('get_start')->willReturn(DateTime::createFromFormat('U', (string) mktime(0, 0, 0)));
		$mock->method('get_end')->willReturn(DateTime::createFromFormat('U', (string) mktime(1, 0, 0)));

		return $mock;
	}

	public function setUp() {
		$this->sent = $this->createMockBandwidthObject();
		$this->received = $this->createMockBandwidthObject();
		$this->traffic = new traffic($this->sent, $this->received);
	}

	public function tearDown() {
		unset($this->sent);
		unset($this->received);
		unset($this->traffic);
	}

	public function testGetProperties() {
		// NOTE: assertSame checks that both values are a reference to the same object and not just that they are equal
		$this->assertSame($this->sent, $this->traffic->get_sent());
		$this->assertSame($this->received, $this->traffic->get_received());
	}

	public function testGetStartTime() {
		// BandwidthTest verifies that the correct object is returned
		// from get_start() so just check the instance type here
		$this->assertInstanceOf(\DateTime::class, $this->traffic->get_start());
	}

	public function testGetEndTime() {
		// BandwidthTest verifies that the correct object is returned
		// from get_end() so just check the instance type here
		$this->assertInstanceOf(\DateTime::class, $this->traffic->get_end());
	}

}
