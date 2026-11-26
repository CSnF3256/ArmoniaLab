<?php

namespace App\Services;

use App\Contracts\ExerciseEvaluator;
use App\Services\Exercises\Evaluators\IntervalEvaluator;
use App\Services\Exercises\Evaluators\MultipleChoiceEvaluator;
use App\Services\Exercises\Evaluators\NoteOrderEvaluator;
use App\Services\Exercises\Evaluators\TrueFalseEvaluator;
use InvalidArgumentException;

class ExerciseEvaluatorResolver
{
    /** @var array<string, class-string<ExerciseEvaluator>> */
    private const STRATEGIES = [
        'clasica' => MultipleChoiceEvaluator::class,
        'completar_secuencia' => MultipleChoiceEvaluator::class,
        'seleccionar_intervalo' => IntervalEvaluator::class,
        'verdadero_falso' => TrueFalseEvaluator::class,
        'ordenar_notas' => NoteOrderEvaluator::class,
        'detectar_error' => MultipleChoiceEvaluator::class,
        'construccion_visual' => MultipleChoiceEvaluator::class,
        'progresion_funcional' => MultipleChoiceEvaluator::class,
        'sincronia_ritmica' => MultipleChoiceEvaluator::class,
    ];

    /** @var array<string, ExerciseEvaluator> */
    private array $instances = [];

    public function __construct(private readonly \Illuminate\Contracts\Container\Container $container) {}

    public function resolve(string $modality): ExerciseEvaluator
    {
        $strategy = self::STRATEGIES[$modality] ?? null;

        if ($strategy === null) {
            throw new InvalidArgumentException("No hay evaluador configurado para la modalidad [{$modality}].");
        }

        return $this->instances[$modality] ??= $this->container->make($strategy);
    }

    public function supportedModalities(): array
    {
        return array_keys(self::STRATEGIES);
    }
}
