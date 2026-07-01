<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Cnpj implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cnpj = preg_replace('/\D/', '', (string) $value);

        if (! self::isValid($cnpj)) {
            $fail('O :attribute informado é inválido.');
        }
    }

    public static function isValid(string $cnpj): bool
    {
        if (strlen($cnpj) !== 14 || preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        $calculateDigit = static function (string $base, array $weights): int {
            $sum = 0;

            foreach ($weights as $index => $weight) {
                $sum += ((int) $base[$index]) * $weight;
            }

            $remainder = $sum % 11;

            return $remainder < 2 ? 0 : 11 - $remainder;
        };

        $first = $calculateDigit(substr($cnpj, 0, 12), [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);
        $second = $calculateDigit(substr($cnpj, 0, 12).$first, [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);

        return $cnpj[12] === (string) $first && $cnpj[13] === (string) $second;
    }
}
