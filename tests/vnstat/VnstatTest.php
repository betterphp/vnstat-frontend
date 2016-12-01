<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use betterphp\vnstat_frontend\data\traffic;
use betterphp\vnstat_frontend\network\network_interface;
use betterphp\vnstat_frontend\vnstat\vnstat;

class VnstatTest extends TestCase {

    private $vnstat;
    private $mock_vnstat_script;

    public function setUp() {
        // Don't like this but we need a real interface to run vnstat commands on for now
        $this->vnstat = network_interface::get_all()[0]->get_vnstat();
        $this->mock_vnstat_script = './mocked-vnstat.sh';
    }

    public function tearDown() {
        // Delete the mocked vnstat if it's been used
        if (file_exists($this->mock_vnstat_script)) {
            unlink($this->mock_vnstat_script);
        }
    }

    private function setNextCommandOutput(array $output_lines) {
        // If the script already exists then we've used a name that's actually part of the project
        if (file_exists($this->mock_vnstat_script)) {
            throw new \Exception('Mock vnstat script already exists');
        }

        // Create a script to output the defined text to stdout
        $output_text = implode("\n", $output_lines);

        $script = <<<SCRIPT
#!/bin/bash

cat <<EOT
{$output_text}
EOT
SCRIPT;

        file_put_contents($this->mock_vnstat_script, $script);
        chmod($this->mock_vnstat_script, 0700);

        // Then set the path to that as the vnstat command
        $property = new \ReflectionProperty($this->vnstat, 'vnstat_command');
        $property->setAccessible(true);
        $property->setValue($this->vnstat, $this->mock_vnstat_script);
    }

    public function testGetVnstatDataInvalidType() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid data type');

        $method = new \ReflectionMethod($this->vnstat, 'get_vnstat_data');
        $method->setAccessible(true);

        $method->invokeArgs($this->vnstat, ['pickle']);
    }

    /**
     * @dataProvider dataGetPeriodTraffic
     */
    public function testGetPeriodTraffic($period) {
        $method_name = "get_{$period}_traffic";
        $results = $this->vnstat->$method_name();

        $this->assertInternalType('array', $results);
        $this->assertNotEmpty($results);

        $expected_duration_h = ($period === 'hourly') ? 1 : 0;
        $expected_duration_m = 0;
        $expected_duration_s = 0;
        $expected_duration_d = ($period === 'daily') ? 1 : 0;
        $expected_duration_m = ($period === 'monthly') ? 1 : 0;
        $expected_duration_y = 0;

        foreach ($results as $result) {
            $this->assertInstanceOf(traffic::class, $result);

            $duration = $result->get_end()->diff($result->get_start());

            $this->assertSame($expected_duration_h, $duration->h);
            $this->assertSame($expected_duration_m, $duration->m);
            $this->assertSame($expected_duration_s, $duration->s);
            $this->assertSame($expected_duration_d, $duration->d);
            $this->assertSame($expected_duration_m, $duration->m);
            $this->assertSame($expected_duration_y, $duration->y);
        }
    }

    public function dataGetPeriodTraffic(): array {
        return [
            ['hourly'],
            ['daily'],
            ['monthly'],
        ];
    }

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

        $start = new DateTime('now');
        $end = new DateTime('+1 hour');

        $result = $this->callParseLiveSampleLine($sample_line, $start, $end);

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

    public function testSampleWithVnstatError() {
        $test_error_message = 'what a fantastic message!';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("vnstat executable returned an error: {$test_error_message}");

        $this->setNextCommandOutput([
            $test_error_message,
        ]);

        $this->vnstat->sample(2);
    }

}
