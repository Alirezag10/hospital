<?php

namespace app\helpers;

/** Applies a Gregorian day received from the local Persian calendar. */
final class DateFilter
{
    public static function apply($query, $column, $value)
    {
        if ($value === null || trim((string) $value) === '') {
            return;
        }

        $value = trim((string) $value);
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if ($date !== false && $date->format('Y-m-d') === $value) {
            $query->andWhere(['>=', $column, $value . ' 00:00:00']);
            $query->andWhere(['<', $column, $date->modify('+1 day')->format('Y-m-d') . ' 00:00:00']);
            return;
        }

        // Retain compatibility with an exact timestamp in an older bookmarked URL.
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $value);
        if ($date !== false && $date->format('Y-m-d H:i:s') === $value) {
            $query->andWhere([$column => $value]);
            return;
        }

        $query->andWhere('0=1');
    }
}
