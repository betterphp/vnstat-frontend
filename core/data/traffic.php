<?php

declare(strict_types=1);

namespace betterphp\vnstat_frontend\data;

use betterphp\vnstat_frontend\data\bandwidth;

class traffic {

    private $sent;
    private $received;

    /**
     * We call bandwidth in both directions traffic for lack of a better name *shrug*
     *
     * @param bandwidth $sent The sent bandwidth
     * @param bandwidth $received The received bandwidth
     */
    public function __construct(bandwidth $sent, bandwidth $received) {
        $this->sent = $sent;
        $this->received = $received;

        // The time period must be the same for both measurements
        // != instead of !== is on purpose here as they are not the same object
        if ($sent->get_start() != $received->get_start()) {
            throw new \Exception('The start time is not the same for both measurements');
        }

        if ($sent->get_end() != $received->get_end()) {
            throw new \Exception('The end time is not the same for both measurements');
        }
    }

    /**
    * Gets the sent bandwidth
    *
    * @return bandwidth The bandwidth
    */
    public function get_sent(): bandwidth {
        return $this->sent;
    }

    /**
     * Gets the received bandwidth
     *
     * @return bandwidth The bandwidth
     */
    public function get_received(): bandwidth {
        return $this->received;
    }

    /**
     * Gets the start of the measurement range
     *
     * Note that internally this will return the value from the sent result
     *
     * @return \DateTime The time
     */
    public function get_start(): \DateTime {
        return $this->sent->get_start();
    }

    /**
     * Gets the end of the measurement range
     *
     * Note that internally this will return the value from the sent result
     *
     * @return \DateTime The time
     */
    public function get_end(): \DateTime {
        return $this->sent->get_end();
    }

}
