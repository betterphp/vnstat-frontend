<?php

declare(strict_types=1);

namespace betterphp\vnstat_frontend\data;

class bandwidth {

	private $byte_rate;
	private $packet_rate;
	private $start;
	private $end;

	/**
	 * Creates a new bandwith data object. All numbers are per second averaged over the defined time period
	 *
	 * @param integer $byte_rate The number of bytes transfered
	 * @param integer $packet_rate The number of packets sent
	 * @param \DateTime $start The start of the time period
	 * @param \DateTime $end The end of the time period
	 */
	public function __construct(int $byte_rate, int $packet_rate, \DateTime $start, \DateTime $end) {
		$this->byte_rate = $byte_rate;
		$this->packet_rate = $packet_rate;
		$this->start = $start;
		$this->end = $end;

		if ($start >= $end) {
			throw new \Exception('End time not after start time');
		}
	}

	/**
	 * Gets the average number of bytes per second transfered during the time range
	 *
	 * @return int the number
	 */
	public function get_byte_rate(): int {
		return $this->byte_rate;
	}

	/**
	 * Gets the average number of packets per second during the time range
	 *
	 * @return int The number of packets
	 */
	public function get_packet_rate(): int {
		return $this->packet_rate;
	}

	/**
	 * Gets the time that the range started
	 *
	 * @return DateTime The time
	 */
	public function get_start(): \DateTime {
		return $this->start;
	}

	/**
	 * Gets the time that the range ends
	 *
	 * @return Datetime The time
	 */
	public function get_end(): \Datetime {
		return $this->end;
	}

}
