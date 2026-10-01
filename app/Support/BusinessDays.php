<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * Brazilian *national* holidays only (no state/municipal calendar - that
 * would need a per-customer city mapping, out of scope for now). Used to
 * compute the grace period after an invoice's due date before its license
 * gets suspended for non-payment.
 */
class BusinessDays
{
    public static function addBusinessDays(Carbon $date, int $days): Carbon
    {
        $result = $date->copy();

        while ($days > 0) {
            $result->addDay();

            if (! static::isWeekendOrHoliday($result)) {
                $days--;
            }
        }

        return $result;
    }

    public static function isWeekendOrHoliday(Carbon $date): bool
    {
        return $date->isWeekend() || static::isHoliday($date);
    }

    public static function isHoliday(Carbon $date): bool
    {
        return in_array($date->format('Y-m-d'), static::holidaysForYear($date->year), true);
    }

    /**
     * @return array<int, string> dates in Y-m-d format
     */
    protected static function holidaysForYear(int $year): array
    {
        $fixed = [
            "$year-01-01", // Confraternização Universal
            "$year-04-21", // Tiradentes
            "$year-05-01", // Dia do Trabalho
            "$year-09-07", // Independência
            "$year-10-12", // Nossa Senhora Aparecida
            "$year-11-02", // Finados
            "$year-11-15", // Proclamação da República
            "$year-11-20", // Consciência Negra
            "$year-12-25", // Natal
        ];

        $easter = static::easterDate($year);

        $movable = [
            $easter->copy()->subDays(47)->format('Y-m-d'), // Carnaval (terça)
            $easter->copy()->subDays(2)->format('Y-m-d'),  // Sexta-feira Santa
            $easter->copy()->addDays(60)->format('Y-m-d'), // Corpus Christi
        ];

        return array_merge($fixed, $movable);
    }

    /**
     * Pure-PHP Easter Sunday calculation (Meeus/Jones/Butcher Gregorian
     * algorithm) - ext-calendar's easter_date() isn't available on this
     * build, so this avoids that dependency entirely.
     */
    protected static function easterDate(int $year): Carbon
    {
        $a = $year % 19;
        $b = intdiv($year, 100);
        $c = $year % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $month = intdiv($h + $l - 7 * $m + 114, 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;

        return Carbon::createFromDate($year, $month, $day)->startOfDay();
    }
}
