<?php
namespace app\components;

/** Server-side Persian calendar conversion using ICU. */
final class JalaliDate
{
    private static function calendar(): \IntlCalendar
    {
        return \IntlCalendar::createInstance('Asia/Tehran', 'fa_IR@calendar=persian');
    }

    public static function fromGregorian(int $year, int $month, int $day): array
    {
        $calendar = self::calendar();
        $date = new \DateTimeImmutable(sprintf('%04d-%02d-%02d', $year, $month, $day), new \DateTimeZone('Asia/Tehran'));
        $calendar->setTime($date->getTimestamp() * 1000);
        return [$calendar->get(\IntlCalendar::FIELD_YEAR), $calendar->get(\IntlCalendar::FIELD_MONTH) + 1, $calendar->get(\IntlCalendar::FIELD_DAY_OF_MONTH)];
    }

    public static function toGregorian(int $year, int $month, int $day): \DateTimeImmutable
    {
        $calendar = self::calendar();
        $calendar->setLenient(false);
        $calendar->clear();
        // Noon avoids rejecting a valid date when a historical DST change skipped midnight.
        $calendar->set($year, $month - 1, $day, 12, 0, 0);
        $time = $calendar->getTime();
        if ($time === false) {
            throw new \InvalidArgumentException('Invalid Persian date.');
        }
        return (new \DateTimeImmutable('@' . (int) ($time / 1000)))
            ->setTimezone(new \DateTimeZone('Asia/Tehran'))->setTime(0, 0);
    }

    public static function parse($value): ?string
    {
        if (!is_string($value)) {
            return null;
        }
        $value = \app\models\Patient::normalizeDigits($value);
        if (!preg_match('/^(1[2-5]\d{2}|1600)[\/-](\d{1,2})[\/-](\d{1,2})$/D', $value, $parts)) {
            return null;
        }
        try {
            return self::toGregorian((int) $parts[1], (int) $parts[2], (int) $parts[3])->format('Y-m-d');
        } catch (\InvalidArgumentException $error) {
            return null;
        }
    }
}
