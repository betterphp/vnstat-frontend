<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use betterphp\utils\reflection;
use betterphp\vnstat_frontend\network\network_interface;
use betterphp\vnstat_frontend\vnstat\vnstat;

/**
 * @covers betterphp\vnstat_frontend\network\network_interface
 */
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
        $names = reflection::call_method(network_interface::class, 'get_all_names');

        foreach ($names as $name) {
            $interface = new network_interface($name);

            $this->assertSame($name, $interface->get_name());
        }
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
