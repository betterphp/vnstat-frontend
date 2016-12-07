<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use betterphp\vnstat_frontend\data\bandwidth;
use betterphp\vnstat_frontend\data\traffic;

/**
 * @covers betterphp\vnstat_frontend\data\traffic
 */
class TrafficTest extends TestCase {

    private function createMockBandwidthObject(DateTime $start, DateTime $end) {
        // Create a mock bandwidth object - a bit overkil maybe but want to only test the traffic class here
        $mock = $this->getMockBuilder(bandwidth::class)
                     ->disableOriginalConstructor()
                     ->getMock();

        $mock->method('get_start')->willReturn($start);
        $mock->method('get_end')->willReturn($end);

        return $mock;
    }

    private function createValidTrafficObject(): array {
        $start = DateTime::createFromFormat('U', (string) mktime(0, 0, 0));
        $end = DateTime::createFromFormat('U', (string) mktime(1, 0, 0));

        $sent = $this->createMockBandwidthObject($start, $end);
        $received = $this->createMockBandwidthObject($start, $end);

        return [$sent, $received, new traffic($sent, $received)];
    }

    public function testMismatchedStartDates() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The start time is not the same for both measurements');

        $start_one = DateTime::createFromFormat('U', (string) mktime(0, 0, 0));
        $start_two = DateTime::createFromFormat('U', (string) mktime(0, 30, 0));
        $end = DateTime::createFromFormat('U', (string) mktime(1, 0, 0));

        $sent = $this->createMockBandwidthObject($start_one, $end);
        $received = $this->createMockBandwidthObject($start_two, $end);

        new traffic($sent, $received);
    }

    public function testMismatchedEndDates() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The end time is not the same for both measurements');

        $start = DateTime::createFromFormat('U', (string) mktime(0, 0, 0));
        $end_one = DateTime::createFromFormat('U', (string) mktime(1, 0, 0));
        $end_two = DateTime::createFromFormat('U', (string) mktime(1, 30, 0));

        $sent = $this->createMockBandwidthObject($start, $end_one);
        $received = $this->createMockBandwidthObject($start, $end_two);

        new traffic($sent, $received);
    }

    public function testGetSent() {
        list($sent, $received, $traffic) = $this->createValidTrafficObject();

        $this->assertSame($sent, $traffic->get_sent());
    }

    public function testGetReceived() {
        list($sent, $received, $traffic) = $this->createValidTrafficObject();

        $this->assertSame($received, $traffic->get_received());
    }

    public function testGetStartTime() {
        list($sent, $received, $traffic) = $this->createValidTrafficObject();

        // BandwidthTest verifies that the correct object is returned
        // from get_start() so just check the instance type here
        $this->assertInstanceOf(\DateTime::class, $traffic->get_start());
    }

    public function testGetEndTime() {
        list($sent, $received, $traffic) = $this->createValidTrafficObject();

        // BandwidthTest verifies that the correct object is returned
        // from get_end() so just check the instance type here
        $this->assertInstanceOf(\DateTime::class, $traffic->get_end());
    }

}
