<?php

namespace App\Services;

use App\Models\TipoAcorde;

class AcordeService
{
    /**
     * Las 12 clases cromáticas de notas musicales en notación latina y americana.
     */
    public const NOTAS_LATINAS = ['Do', 'Do#', 'Re', 'Re#', 'Mi', 'Fa', 'Fa#', 'Sol', 'Sol#', 'La', 'La#', 'Si'];
    public const NOTAS_ANGLO = ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'];

    public const ENARMONIAS = [
        'Reb' => 'Do#',
        'Mib' => 'Re#',
        'Solb' => 'Fa#',
        'Lab' => 'Sol#',
        'Sib' => 'La#',
        'Db' => 'C#',
        'Eb' => 'D#',
        'Gb' => 'F#',
        'Ab' => 'G#',
        'Bb' => 'A#',
    ];

    public const NOMBRES_INTERVALOS = [
        0 => 'Tónica / Fundamental (1P)',
        1 => 'Segunda menor (2m / 1 st)',
        2 => 'Segunda mayor (2M / 2 st)',
        3 => 'Tercera menor (3m / 3 st)',
        4 => 'Tercera mayor (3M / 4 st)',
        5 => 'Cuarta justa (4J / 5 st)',
        6 => 'Tritono / 5ta disminuida (b5 / 6 st)',
        7 => 'Quinta justa (5J / 7 st)',
        8 => 'Quinta aumentada / 6ta menor (b6 / 8 st)',
        9 => 'Sexta mayor (6M / 9 st)',
        10 => 'Séptima menor (7m / 10 st)',
        11 => 'Séptima mayor (7M / 11 st)',
        12 => 'Octava justa (8P / 12 st)',
        14 => 'Novena mayor (9M / 14 st)',
    ];

    /**
     * Construye y calcula las notas de un acorde musical.
     *
     * @param string $fundamental Nota fundamental (ej: 'Do', 'C', 'Fa#', 'G')
     * @param TipoAcorde|int|array $tipoAcorde Modelo TipoAcorde, ID, o arreglo de semitonos
     * @return array Detalle completo con notas, intervalos, pasos matemáticos y teclas de teclado
     */
    public function construir(string $fundamental, TipoAcorde|int|array $tipoAcorde): array
    {
        $fundamentalNormalizada = $this->normalizarNota($fundamental);
        $indiceFundamental = array_search($fundamentalNormalizada, self::NOTAS_LATINAS);

        if ($indiceFundamental === false) {
            throw new \InvalidArgumentException("La nota fundamental '{$fundamental}' no es válida.");
        }

        if ($tipoAcorde instanceof TipoAcorde) {
            $modelo = $tipoAcorde;
            $semitonos = $modelo->intervalos_array;
            $nombreTipo = $modelo->nombre;
            $simbolo = $modelo->simbolo;
            $tipoId = $modelo->id;
        } elseif (is_numeric($tipoAcorde)) {
            $modelo = TipoAcorde::findOrFail($tipoAcorde);
            $semitonos = $modelo->intervalos_array;
            $nombreTipo = $modelo->nombre;
            $simbolo = $modelo->simbolo;
            $tipoId = $modelo->id;
        } elseif (is_array($tipoAcorde)) {
            $semitonos = $tipoAcorde;
            $nombreTipo = 'Personalizado';
            $simbolo = '';
            $tipoId = null;
        } else {
            throw new \InvalidArgumentException("Tipo de acorde no reconocido.");
        }

        $notasResultantes = [];
        $desgloseIntervalos = [];
        $pasosCalculo = [];
        $teclasActivas = [];

        foreach ($semitonos as $semitono) {
            $posicionCromatica = ($indiceFundamental + $semitono) % 12;
            $notaLatina = self::NOTAS_LATINAS[$posicionCromatica];
            $notaAnglo = self::NOTAS_ANGLO[$posicionCromatica];

            $notasResultantes[] = $notaLatina;

            $desgloseIntervalos[] = [
                'semitono' => $semitono,
                'nombre_intervalo' => self::NOMBRES_INTERVALOS[$semitono] ?? "{$semitono} semitonos",
                'nota' => $notaLatina,
                'nota_anglo' => $notaAnglo,
            ];

            $pasosCalculo[] = [
                'formula' => "({$indiceFundamental} [{$fundamentalNormalizada}] + {$semitono} st) mod 12 = {$posicionCromatica} -> {$notaLatina}",
                'semitono' => $semitono,
                'resultado' => $notaLatina,
            ];

            // Posición en teclado (primer octava y opcionalmente segunda octava para visualización realista)
            $teclasActivas[] = $posicionCromatica;
        }

        $nombreCompleto = "{$fundamentalNormalizada} {$nombreTipo}";
        $cifradoCompleto = self::NOTAS_ANGLO[$indiceFundamental] . ($simbolo === 'M' ? '' : $simbolo);

        return [
            'fundamental' => $fundamentalNormalizada,
            'fundamental_anglo' => self::NOTAS_ANGLO[$indiceFundamental],
            'tipo_id' => $tipoId,
            'tipo_nombre' => $nombreTipo,
            'simbolo' => $simbolo,
            'nombre_completo' => $nombreCompleto,
            'cifrado_americano' => $cifradoCompleto,
            'notas' => $notasResultantes,
            'notas_texto' => implode(' - ', $notasResultantes),
            'semitonos' => $semitonos,
            'desglose' => $desgloseIntervalos,
            'pasos_calculo' => $pasosCalculo,
            'teclas_activas' => array_values(array_unique($teclasActivas)),
        ];
    }

    /**
     * Normaliza nombres de notas a notación estándar latina con sostenidos.
     */
    public function normalizarNota(string $nota): string
    {
        $nota = trim(ucfirst(strtolower($nota)));

        if (isset(self::ENARMONIAS[$nota])) {
            $nota = self::ENARMONIAS[$nota];
        }

        // Si viene en inglés (C, D#, etc.), traducir a latina
        $indiceAnglo = array_search(strtoupper($nota), self::NOTAS_ANGLO);
        if ($indiceAnglo !== false) {
            return self::NOTAS_LATINAS[$indiceAnglo];
        }

        return $nota;
    }
}
