<?php

declare(strict_types=1);

namespace betterphp\vnstat_frontend\vnstat;

use betterphp\vnstat_frontend\data\traffic;
use betterphp\vnstat_frontend\data\bandwidth;
use betterphp\vnstat_frontend\network\network_interface;

class vnstat {

    private $interface;
    private $vnstat_command;

    /**
     * @param network_interface $interface The interface to work with
     */
    public function __construct(network_interface $interface) {
        // No need to check if this is a valid name since the constructor
        // for network_interface will have done that already.
        $this->interface = $interface;
        $this->vnstat_command = 'vnstat';
    }

    /**
     * Gets the network interface that this vnstat is working on
     *
     * @return network_interface The interface
     */
    public function get_interface(): network_interface {
        return $this->interface;
    }

    /**
     * Used to parse the command output
     *
     * The result format varies depending on the type used
     *
     * @param string $type The group of data to filter to; h, d or m
     *
     * @return array A list of database entries
     */
    private function get_vnstat_data(string $type): array {
        $type_name_map = [
            'h' => 'hours',
            'd' => 'days',
            'm' => 'months',
        ];

        if (!array_key_exists($type, $type_name_map)) {
            throw new \Exception('Invalid data type');
        }

        $cmd_arg_ifname = escapeshellarg($this->interface->get_name());

        $json = shell_exec("{$this->vnstat_command} --json {$type} -i {$cmd_arg_ifname}");
        $data = @json_decode($json); // Ignore errors here and check below

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Command returned invalid JSON');
        }

        // Output is a filtered list of interfaces so just pick the first one
        $data = $data->interfaces[0];

        if (!isset($data->traffic)) {
            throw new \Exception('Command did not return any traffic data');
        }

        $type_property = $type_name_map[$type];

        return $data->traffic->{$type_property};
    }

    /**
     * Used to prepare a list of traffic measurements
     *
     * @param string $type The period length; h, d or m
     *
     * @return array A list of traffic measurements
     */
    private function get_traffic(string $type): array {
        $results = [];

        foreach ($this->get_vnstat_data($type) as $entry) {
            $date = $entry->date;

            // get_vnstat_data will throw an exception for an invalid type
            // so there is no need to validate here as well.
            switch ($type) {
                case 'h':
                    $start_timestamp = mktime($entry->id, 0, 0, $date->month, $date->day, $date->year);
                    $end_timestamp = mktime(($entry->id + 1), 0, 0, $date->month, $date->day, $date->year);
                break;
                case 'd':
                    $start_timestamp = mktime(0, 0, 0, $date->month, $date->day, $date->year);
                    $end_timestamp = mktime(0, 0, 0, $date->month, ($date->day + 1), $date->year);
                break;
                case 'm':
                    $start_timestamp = mktime(0, 0, 0, $date->month, 1, $date->year);
                    $end_timestamp = mktime(0, 0, 0, ($date->month + 1), 1, $date->year);
                break;
            }

            $start = \DateTime::createFromFormat('U', (string) $start_timestamp);
            $end = \DateTime::createFromFormat('U', (string) $end_timestamp);

            $results[] = new traffic(
                new bandwidth($entry->tx, 0, $start, $end),
                new bandwidth($entry->rx, 0, $start, $end)
            );
        }

        usort($results, function ($a, $b) {
            return ($a->get_start() <=> $b->get_start());
        });

        return $results;
    }

    /**
     * Gets the hourly traffic information for the last 24 hours
     *
     * @return array A list of traffic measurements
     */
    public function get_hourly_traffic(): array {
        return $this->get_traffic('h');
    }

    /**
     * Gets the daily traffic information for the last 30 days
     *
     * @return array A list of traffic measurements
     */
    public function get_daily_traffic(): array {
        return $this->get_traffic('d');
    }

    /**
     * Gets the monthly traffic information for the last 12 months
     *
     * @return array A list of traffic measurements
     */
    public function get_monthly_traffic(): array {
        return $this->get_traffic('m');
    }

    /**
     * Used to parse the text output from vnstat live sample command
     *
     * @param string $line The output line
     * @param \DateTime $start The time that the measurement started
     * @param \DateTime $end The time that the measurement completed
     *
     * @return bandwidth The resulting bandwidth measurement
     */
    private function parse_live_sample_line(string $line, \DateTime $start, \DateTime $end): bandwidth {
        $parts = preg_split('#\s+#', trim($line));
        $rate = floatval($parts[1]);
        $packets = intval($parts[3]);

        // The minimum unit vnstat reports is KiB/s
        $rate *= 1024;

        switch ($parts[2]) {
            case 'MiB/s':
                $rate *= 1024;
            break;
            case 'GiB/s':
                $rate *= (1024 * 1024);
            break;
        }

        return new bandwidth((int) $rate, $packets, $start, $end);
    }

    /**
     * Samples the traffic on the interface for a number of seconds and takes an average
     *
     * @param integer $duration How long to take the measurement over
     *
     * @return traffic The resulting measurement.
     */
    public function sample(int $duration): traffic {
        if ($duration < 2) {
            throw new \Exception('Sample duration below 2 seconds');
        }

        $cmd_arg_time = escapeshellarg((string) $duration);
        $cmd_arg_ifname = escapeshellarg($this->interface->get_name());

        $start = new \DateTime();
        $command_output = shell_exec("{$this->vnstat_command} -tr {$cmd_arg_time} -ru 0 -i {$cmd_arg_ifname}");
        $end = new \DateTime();

        $data = explode("\n", trim($command_output));

        if (count($data) !== 5) {
            throw new \Exception('vnstat executable returned an error: ' . end($data));
        }

        $sent_line = $data[4];
        $received_line = $data[3];

        $sent = $this->parse_live_sample_line($sent_line, $start, $end);
        $received = $this->parse_live_sample_line($received_line, $start, $end);

        return new traffic($sent, $received);
    }

}
