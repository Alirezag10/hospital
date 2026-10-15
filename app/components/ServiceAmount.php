<?php
namespace app\components;

final class ServiceAmount
{
    public static function fromDatabase($value): ?int
    {
        $digits = (string) $value;
        if (!preg_match('/^[0-9]+$/D', $digits)) {
            return null;
        }
        $digits = ltrim($digits, '0');
        $digits = $digits === '' ? '0' : $digits;
        $max = (string) PHP_INT_MAX;
        if (strlen($digits) > strlen($max) || (strlen($digits) === strlen($max) && strcmp($digits, $max) > 0)) {
            return null;
        }
        return (int) $digits;
    }

    public static function line($quantity, $price): ?int
    {
        $quantity = self::fromDatabase($quantity);
        $price = self::fromDatabase($price);
        if ($quantity === null || $price === null || ($price > 0 && $quantity > intdiv(PHP_INT_MAX, $price))) {
            return null;
        }
        return $quantity * $price;
    }

    public static function add(?int $total, ?int $amount): ?int
    {
        if ($total === null || $amount === null || $amount > PHP_INT_MAX - $total) {
            return null;
        }
        return $total + $amount;
    }
}
