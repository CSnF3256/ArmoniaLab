<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidEcuadorianCedula implements ValidationRule
{
    /**
     * Valida una cédula de identidad ecuatoriana usando el algoritmo oficial de módulo 10.
     * Esta validación es ejecutada estrictamente en el Back-End antes de interactuar con la base de datos.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cedula = trim((string) $value);

        // 1. Longitud exacta y solo dígitos
        if (! preg_match('/^[0-9]{10}$/', $cedula)) {
            $fail('La cédula debe contener exactamente 10 dígitos numéricos.');
            return;
        }

        // 2. Validación de código de provincia (01 a 24, o 30 para ecuatorianos en el exterior)
        $provincia = (int) substr($cedula, 0, 2);
        if (($provincia < 1 || $provincia > 24) && $provincia !== 30) {
            $fail('Los dos primeros dígitos de la cédula no corresponden a una provincia válida de Ecuador (01-24).');
            return;
        }

        // 3. Tercer dígito para personas naturales debe ser menor a 6
        $tercerDigito = (int) $cedula[2];
        if ($tercerDigito >= 6) {
            $fail('El tercer dígito de la cédula de persona natural debe ser menor a 6.');
            return;
        }

        // 4. Algoritmo de Módulo 10 del Registro Civil
        $coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
        $suma = 0;

        for ($i = 0; $i < 9; $i++) {
            $producto = (int) $cedula[$i] * $coeficientes[$i];
            if ($producto >= 10) {
                $producto -= 9;
            }
            $suma += $producto;
        }

        $residuo = $suma % 10;
        $digitoVerificadorCalculado = ($residuo === 0) ? 0 : 10 - $residuo;
        $digitoVerificadorReal = (int) $cedula[9];

        if ($digitoVerificadorCalculado !== $digitoVerificadorReal) {
            $fail("La cédula ingresada ({$cedula}) es inválida: no supera la verificación algorítmica de módulo 10 del Registro Civil.");
        }
    }
}
