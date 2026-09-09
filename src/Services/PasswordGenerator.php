<?php
namespace Services;

class PasswordGenerator {
    public static function generate(array $params): array {
        $length = $params['length'] ?? 16;
        $includeUpper = $params['includeUppercase'] ?? true;
        $includeLower = $params['includeLowercase'] ?? true;
        $includeNumbers = $params['includeNumbers'] ?? true;
        $includeSymbols = $params['includeSymbols'] ?? true;
        $excludeSimilar = $params['excludeSimilarCharacters'] ?? false;
        $count = $params['count'] ?? 1;

        if ($length < 8 || $length > 128) {
            throw new \InvalidArgumentException("La longitud debe estar entre 8 y 128 caracteres.");
        }

        $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lower = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*()_+-=[]{}|;:,.<>?';

        if ($excludeSimilar) {
            $upper = str_replace(['I', 'O'], '', $upper);
            $lower = str_replace(['l', 'o'], '', $lower);
            $numbers = str_replace(['1', '0'], '', $numbers);
        }

        $pool = '';
        if ($includeUpper) $pool .= $upper;
        if ($includeLower) $pool .= $lower;
        if ($includeNumbers) $pool .= $numbers;
        if ($includeSymbols) $pool .= $symbols;

        if (empty($pool)) {
            throw new \InvalidArgumentException("Debe habilitar al menos un tipo de carácter.");
        }

        $passwords = [];
        for ($c = 0; $c < $count; $c++) {
            $pass = '';
            $poolLength = strlen($pool);
            for ($i = 0; $i < $length; $i++) {
                $pass .= $pool[random_int(0, $poolLength - 1)];
            }
            $passwords[] = $pass;
        }

        return [
            'passwords' => $passwords,
            'criteria' => [
                'length' => $length,
                'includeUppercase' => $includeUpper,
                'includeLowercase' => $includeLower,
                'includeNumbers' => $includeNumbers,
                'includeSymbols' => $includeSymbols,
                'excludeSimilarCharacters' => $excludeSimilar,
                'excludeAmbiguousSymbols' => $params['excludeAmbiguousSymbols'] ?? false,
                'count' => $count
            ]
        ];
    }
}
