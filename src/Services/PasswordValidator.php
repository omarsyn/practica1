<?php
namespace Services;

class PasswordValidator {
    public static function validate(string $password, ?string $username = null): array {
        $rules = [];
        $suggestions = [];
        $score = 0;

        // Rule 1: Min length 12
        $passLength = strlen($password) >= 12;
        $rules[] = ['rule' => 'minLength', 'description' => 'Debe tener al menos 12 caracteres', 'passed' => $passLength];
        if ($passLength) $score += 25; else $suggestions[] = "Aumenta la longitud a al menos 12 caracteres.";

        // Rule 2: Upper
        $hasUpper = (bool) preg_match('/[A-Z]/', $password);
        $rules[] = ['rule' => 'hasUppercase', 'description' => 'Debe incluir al menos una letra mayúscula', 'passed' => $hasUpper];
        if ($hasUpper) $score += 15; else $suggestions[] = "Agrega al menos una letra mayúscula (A-Z).";

        // Rule 3: Lower
        $hasLower = (bool) preg_match('/[a-z]/', $password);
        $rules[] = ['rule' => 'hasLowercase', 'description' => 'Debe incluir al menos una letra minúscula', 'passed' => $hasLower];
        if ($hasLower) $score += 15; else $suggestions[] = "Agrega al menos una letra minúscula (a-z).";

        // Rule 4: Number
        $hasNum = (bool) preg_match('/[0-9]/', $password);
        $rules[] = ['rule' => 'hasNumber', 'description' => 'Debe incluir al menos un número', 'passed' => $hasNum];
        if ($hasNum) $score += 15; else $suggestions[] = "Agrega al menos un número (0-9).";

        // Rule 5: Symbol
        $hasSym = (bool) preg_match('/[!@#$%^&*()_+\-=\[\]{};:\'",.<>?\/\\|]/', $password);
        $rules[] = ['rule' => 'hasSymbol', 'description' => 'Debe incluir al menos un símbolo especial', 'passed' => $hasSym];
        if ($hasSym) $score += 15; else $suggestions[] = "Agrega al menos un símbolo especial (!, @, #, $).";

        // Rule 6: No Common
        $common = ['123456', 'password', '12345678', 'qwerty', 'contraseña', 'Contraseña123'];
        $notCommon = !in_array(strtolower($password), $common);
        $rules[] = ['rule' => 'noCommonPassword', 'description' => 'No debe ser una contraseña de uso común', 'passed' => $notCommon];
        if ($notCommon) $score += 15; else $suggestions[] = "Evita usar contraseñas comunes o muy predecibles.";

        // Strength calculation
        $isValid = $passLength && $hasUpper && $hasLower && $hasNum && $hasSym && $notCommon;
        $strength = 'muy_debil';
        if ($score >= 90) $strength = 'muy_fuerte';
        elseif ($score >= 75) $strength = 'fuerte';
        elseif ($score >= 50) $strength = 'media';
        elseif ($score >= 25) $strength = 'debil';

        return [
            'password' => $password,
            'isValid' => $isValid,
            'strength' => $strength,
            'score' => $score,
            'rules' => $rules,
            'suggestions' => $suggestions
        ];
    }
}
