<?php
namespace App\Service;

class IntervalGenerator
{
    /**
     * @param \DateTime $startTime
     * @param \DateTime $endTime
     * @param int $interval
     * @return array
     * @throws \Exception
     */
    public static function getIntervalArray(\DateTime $startTime, \DateTime $endTime, int $interval): array
    {
        $startTime = self::roundTime($startTime, $interval, true);
        $endTime = self::roundTime($endTime, $interval, true);

        $timeArray = [];
        while ($startTime <= $endTime) {
            $timeArray[] = $startTime->format('c');
            $startTime->add(new \DateInterval('PT' . $interval . 'M'));
        }

        return $timeArray;
    }

    public static function roundTime(\DateTime $datetime, $precision = 30, $roundLower = false) {
        // 1) Set number of seconds to 0 (by rounding up to the nearest minute if necessary)
        $second = (int) $datetime->format("s");
        if ($second > 30 && $roundLower == false) {
            // Jumps to the next minute
            $datetime->add(new \DateInterval("PT".(60-$second)."S"));
        } elseif ($second > 0) {
            // Back to 0 seconds on current minute
            $datetime->sub(new \DateInterval("PT".$second."S"));
        }
        // 2) Get minute
        $minute = (int) $datetime->format("i");
        // 3) Convert modulo $precision
        $minute = $minute % $precision;
        if ($minute > 0) {
            if($roundLower) {
                $datetime->sub(new \DateInterval("PT".$minute."M"));
            } else {
                // 4) Count minutes to next $precision-multiple minutes
                $diff = $precision - $minute;
                // 5) Add the difference to the original date time
                $datetime->add(new \DateInterval("PT".$diff."M"));
            }
        }

        return $datetime;
    }
}