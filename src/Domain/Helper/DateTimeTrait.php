<?php declare(strict_types=1);
/*
 * This file is part of FlexPHP.
 *
 * (c) Freddie Gar <freddie.gar@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace FlexPHP\Bundle\HelperBundle\Domain\Helper;

use DateInterval;
use DateTime;
use DateTimeZone;

trait DateTimeTrait
{
    private function getDateTime(string $time, ?DateTimeZone $timezone = null): DateTime
    {
        return new DateTime($time, $timezone);
    }

    private function getTimestamp(?DateTimeZone $timezone = null): DateTime
    {
        return $this->getDateTime('now', $timezone);
    }

    private function getOffset(?DateTimeZone $timezone = null): int
    {
        return $this->getTimestamp($timezone)->getOffset();
    }

    private function getOffsetInverted(int $offset): int
    {
        return -1 * $offset;
    }

    private function getTimezone(?string $timezone): DateTimeZone
    {
        return new DateTimeZone($timezone ?? 'UTC');
    }

    private function dateTimeToUTC(?string $datetime, int $offset): ?string
    {
        if ($datetime) {
            $interval = new DateInterval('PT' . \abs($offset) . 'S');

            if ($offset > 0) {
                $datetimeUTC = (new DateTime($datetime))->sub($interval);
            } else {
                $datetimeUTC = (new DateTime($datetime))->add($interval);
            }

            return $datetimeUTC->format('Y-m-d H:i:s');
        }

        return null;
    }

    private function getStartToday(DateTimeZone $timezone, int $offset): string
    {
        $timestamp = $this->getDateTime('today', $timezone);

        if ($offset > 0) {
            $timestamp
                ->sub(new DateInterval('P1D'))
                ->sub(new DateInterval('PT' . $offset . 'S'));
        } elseif ($offset < 0) {
            $timestamp
                ->add(new DateInterval('PT' . \abs($offset) . 'S'));
        }

        return $timestamp->format('Y-m-d H:i:s');
    }

    private function getEndToday(DateTimeZone $timezone, int $offset): string
    {
        $timestamp = $this->getDateTime('today', $timezone);

        if ($offset > 0) {
            $timestamp->sub(new DateInterval('PT' . ($offset + 1) . 'S'));
        } elseif ($offset < 0) {
            $timestamp->add(new DateInterval('P1D'));
            $timestamp->add(new DateInterval('PT' . \abs($offset + 1) . 'S'));
        } else {
            $timestamp->add(new DateInterval('P1D'));
            $timestamp->sub(new DateInterval('PT' . ($offset + 1) . 'S'));
        }

        return $timestamp->format('Y-m-d H:i:s');
    }

    private function getStartDay(string $date, int $offset): string
    {
        return $this->getDateTime($date, new DateTimeZone('UTC'))->modify($offset . ' seconds')->format('Y-m-d H:i:s');
    }

    private function getEndDay(string $date, int $offset): string
    {
        return $this->getDateTime($date, new DateTimeZone('UTC'))->modify($offset . ' seconds')->format('Y-m-d H:i:s');
    }
}
