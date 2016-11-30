<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use betterphp\vnstat_frontend\data\traffic;
use betterphp\vnstat_frontend\network\network_interface;
use betterphp\vnstat_frontend\vnstat\vnstat;

class VnstatTest extends TestCase {

	private $vnstat;
	private $start;
	private $end;

	public function setUp() {
		// Don't like this but we need a real interface to run vnstat commands on for now
		$this->vnstat = network_interface::get_all()[0]->get_vnstat();

		$this->start = new \DateTime('now');
		$this->end = new \DateTime('+1 hour');
	}

	// This is essentially a placeholder test until the return values are refactored.
	// Just call the method and make sure it returns an array for now.

	public function testGetTraffic() {
		$traffic = $this->vnstat->get_traffic();

		$this->assertInternalType('array', $traffic);
	}

	//

	private function callParseLiveSampleLine($line, $start, $end) {
		$method = new \ReflectionMethod($this->vnstat, 'parse_live_sample_line');
		$method->setAccessible(true);

		return $method->invokeArgs($this->vnstat, [&$line, &$start, &$end]);
	}

	/**
	 * @dataProvider dataParseLiveSampleLine
	 */
	public function testParseLiveSampleLine(
		string $displayed_rate,
		string $displayed_unit,
		int $expected_rate,
		int $packet_rate
	) {
		$sample_line = "      rx          {$displayed_rate} {$displayed_unit}             {$packet_rate} packets/s";

		$result = $this->callParseLiveSampleLine($sample_line, $this->start, $this->end);

		$this->assertSame($expected_rate, $result->get_byte_rate());
		$this->assertSame($packet_rate, $result->get_packet_rate());
	}

	public function dataParseLiveSampleLine(): array {
		return [
			['0.50', 'KiB/s', (0.5 * 1024), 10],
			['1.00', 'KiB/s', (1 * 1024), 10],
			['5.00', 'KiB/s', (5 * 1024), 10],
			['120.50', 'KiB/s', (120.5 * 1024), 10],

			['0.50', 'MiB/s', (0.5 * 1024 * 1024), 1245],
			['1.00', 'MiB/s', (1 * 1024 * 1024), 1245],
			['5.00', 'MiB/s', (5 * 1024 * 1024), 1245],
			['120.50', 'MiB/s', (120.5 * 1024 * 1024), 1245],

			['0.50', 'GiB/s', (0.5 * 1024 * 1024 * 1024), 124545],
			['1.00', 'GiB/s', (1 * 1024 * 1024 * 1024), 124545],
			['5.00', 'GiB/s', (5 * 1024 * 1024 * 1024), 124545],
			['120.50', 'GiB/s', (120.5 * 1024 * 1024 * 1024), 124545],
		];
	}

	public function testSample() {
		$live_traffic = $this->vnstat->sample(2);

		$this->assertInstanceOf(traffic::class, $live_traffic);
	}

	/**
	 * @dataProvider dataSampleDuration
	 */
	public function testSampleDuration(int $expected_duration) {
		// Make sure the sample method blocks for the right amount of
		// time and that this matches the start and end time difference
		$start_timestamp = microtime(true);
		$result = $this->vnstat->sample($expected_duration);
		$end_timestamp = microtime(true);

		$date_interval = $result->get_end()->diff($result->get_start());

		$this->assertSame($expected_duration, (int) round($end_timestamp - $start_timestamp));
		$this->assertSame($expected_duration, $date_interval->s);
	}

	public function dataSampleDuration(): array {
		return [
			[2],
			[5],
			[10],
		];
	}

	public function testSampleWithBadDuration() {
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Sample duration below 2 seconds');

		$this->vnstat->sample(1);
	}

}
