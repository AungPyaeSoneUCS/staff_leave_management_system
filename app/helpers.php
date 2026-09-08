<?php

use App\Models\User;

if (! function_exists('my_number')) {
    function my_number(int|float|string $value): string
    {
        if (is_numeric($value)) {
            $value = rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
        }

        if (app()->getLocale() !== 'my') {
            return (string) $value;
        }

        $map = [
            '0' => '၀', '1' => '၁', '2' => '၂', '3' => '၃', '4' => '၄',
            '5' => '၅', '6' => '၆', '7' => '၇', '8' => '၈', '9' => '၉',
        ];

        $result = str_split((string) $value);
        $converted = '';

        foreach ($result as $char) {
            $converted .= $map[$char] ?? $char;
        }

        return $converted;
    }
}

if (! function_exists('my_phone')) {
    function my_phone(?string $phone): string
    {
        if (empty($phone)) {
            return __('common.n_a');
        }

        $digits = preg_replace('/\D/', '', (string) $phone);

        if (str_starts_with($digits, '95')) {
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '09')) {
            $digits = substr($digits, 2);
        }

        return my_number('09-'.$digits);
    }
}

if (! function_exists('get_position_levels')) {
    function get_position_levels(): array
    {
        return config('levels', []);
    }
}

if (! function_exists('get_position_level')) {
    function get_position_level(?string $position): ?int
    {
        if (empty($position)) {
            return null;
        }

        $levels = config('levels', []);

        return $levels[$position] ?? null;
    }
}

if (! function_exists('position_is_higher_or_equal')) {
    function position_is_higher_or_equal(?string $positionA, ?string $positionB): bool
    {
        $levelA = get_position_level($positionA);
        $levelB = get_position_level($positionB);

        if ($levelA === null || $levelB === null) {
            return false;
        }

        return $levelA <= $levelB;
    }
}

if (! function_exists('position_is_lower')) {
    function position_is_lower(?string $positionA, ?string $positionB): bool
    {
        return ! position_is_higher_or_equal($positionA, $positionB);
    }
}

if (! function_exists('get_duty_exchange_candidates')) {
    function get_duty_exchange_candidates(User $user, ?int $excludeUserId = null): array
    {
        if ($user->isStaff() || $user->isDepartmentHead()) {
            $query = User::where('department_id', $user->department_id);

            if ($excludeUserId) {
                $query->where('id', '!=', $excludeUserId);
            }

            $candidates = $query->get()->all();

            return $candidates;
        }

        return [];
    }
}
