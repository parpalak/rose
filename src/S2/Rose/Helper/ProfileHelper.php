<?php
/**
 * @copyright 2016-2020 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Helper;

class ProfileHelper
{
    /**
     * @param string $message
     * @param float  $duration
     *
     * @return array
     */
    public static function getProfilePoint($message, $duration)
    {
        return [
            'message'           => $message,
            'duration'          => $duration,
            'memory_usage'      => memory_get_usage(),
            'memory_peak_usage' => memory_get_peak_usage(),
        ];
    }

    /**
     * @param array $point
     *
     * @return string
     */
    public static function formatProfilePoint(array $point)
    {
        $point['message']           = str_pad($point['message'], 25, ' ', STR_PAD_RIGHT);
        $point['duration']          = str_pad(number_format($point['duration'] * 1000.0, 2, '.', ' ') . ' ms', 20, ' ', STR_PAD_LEFT);
        $point['memory_usage']      = str_pad(number_format($point['memory_usage'], 0, '.', ' '), 20, ' ', STR_PAD_LEFT);
        $point['memory_peak_usage'] = str_pad(number_format($point['memory_peak_usage'], 0, '.', ' '), 20, ' ', STR_PAD_LEFT);

        return implode('', $point);
    }
}
