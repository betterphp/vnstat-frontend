<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use betterphp\vnstat_frontend\network\network_interface;
use betterphp\vnstat_frontend\vnstat\vnstat;

class NetworkInterfaceTest extends TestCase {

    public function testInvalidName() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid interface name');

        new network_interface('hopefully_invalid_name');
    }

    public function testGetName() {
        // Call get_all_names directly with reflection as calling get_all would
        // call the constructor which is what we're testing here, a little pointless
        // but good to have the test coverage.
        $method = new ReflectionMethod(network_interface::class, 'get_all_names');
        $method->setAccessible(true);
        $names = $method->invokeArgs(null, []);

        $interface = new network_interface($names[0]);

        $this->assertSame($names[0], $interface->get_name());
    }

    public function testGetVnstat() {
        $interface = network_interface::get_all()[0];

        // Make sure we get a vnstat back
        $this->assertInstanceOf(vnstat::class, $interface->get_vnstat());
        // and that it thinks it's meant to work on this interface
        $this->assertSame($interface, $interface->get_vnstat()->get_interface());
    }

    public function testGetAll() {
        $interfaces = network_interface::get_all();

        $this->assertInternalType('array', $interfaces);
        $this->assertNotEmpty($interfaces);

        foreach ($interfaces as $interface) {
            $this->assertInstanceOf(network_interface::class, $interface);
        }
    }

}
