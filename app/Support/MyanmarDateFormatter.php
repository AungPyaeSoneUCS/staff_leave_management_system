<?php

namespace App\Support;

use Carbon\Carbon;

class MyanmarDateFormatter
{
    private const MONTHS = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December',
    ];

    private const MONTHS_MM = [
        'ဇန်နဝါရီ', 'ဖေဖော်ဝါရီ', 'မတ်', 'ဧပြီ', 'မေ', 'ဇွန်',
        'ဇူလိုင်', 'ဩဂုတ်', 'စက်တင်ဘာ', 'အောက်တိုဘာ', 'နိုဝင်ဘာ', 'ဒီဇင်ဘာ',
    ];

    private const WEEKDAYS = [
        'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday',
    ];

    private const WEEKDAYS_MM = [
        'တနင်္လာ', 'အင်္ဂါ', 'ဗုဒ္ဓဟူး', 'ကြာသပတေး', 'သောကြာ', 'စနေ', 'တနင်္ဂနွေ',
    ];

    private const AM_PM = ['AM', 'PM'];

    private const AM_PM_MM = ['မနက်', 'နွေ'];

    public static function format(?Carbon $date, string $format): string
    {
        if (is_null($date)) {
            return __('common.n_a');
        }

        if (app()->getLocale() !== 'my') {
            return $date->format($format);
        }

        $result = $date->format($format);

        $result = str_replace(self::MONTHS, self::MONTHS_MM, $result);
        $result = str_replace(self::WEEKDAYS, self::WEEKDAYS_MM, $result);
        $result = str_replace(self::AM_PM, self::AM_PM_MM, $result);

        return my_number($result);
    }

    public static function diffForHumans(Carbon $date): string
    {
        if (app()->getLocale() !== 'my') {
            return $date->diffForHumans();
        }

        $now = Carbon::now();
        $diff = $now->diff($date);

        if ($diff->y > 0) {
            return ($diff->invert ? 'လွန်ခဲ့သော' : 'လာမည့်').' '.my_number($diff->y).' '.($diff->y === 1 ? 'နှစ်' : 'နှစ်');
        }
        if ($diff->m > 0) {
            return ($diff->invert ? 'လွန်ခဲ့သော' : 'လာမည့်').' '.my_number($diff->m).' '.($diff->m === 1 ? 'လ' : 'လ');
        }
        if ($diff->d > 0) {
            return ($diff->invert ? 'လွန်ခဲ့သော' : 'လာမည့်').' '.my_number($diff->d).' '.($diff->d === 1 ? 'ရက်' : 'ရက်');
        }
        if ($diff->h > 0) {
            return ($diff->invert ? 'လွန်ခဲ့သော' : 'လာမည့်').' '.my_number($diff->h).' '.($diff->h === 1 ? 'နာရီ' : 'နာရီ');
        }
        if ($diff->i > 0) {
            return ($diff->invert ? 'လွန်ခဲ့သော' : 'လာမည့်').' '.my_number($diff->i).' '.($diff->i === 1 ? 'မိနစ်' : 'မိနစ်');
        }
        if ($diff->s > 0) {
            return ($diff->invert ? 'လွန်ခဲ့သော' : 'လာမည့်').' '.my_number($diff->s).' '.($diff->s === 1 ? 'စက္ကန့်' : 'စက္ကန့်');
        }

        return __('common.just_now');
    }
}
