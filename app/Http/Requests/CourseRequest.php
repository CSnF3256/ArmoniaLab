<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $course = $this->route('course');

        return $user !== null && ($course === null || $course->user_id === $user->id);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:1000'],
            'level' => ['required', 'in:Principiante,Intermedio,Avanzado'],
            'status' => ['required', 'in:Borrador,Publicado'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Escribe el título del curso.',
            'description.required' => 'Escribe una descripción.',
            'level.in' => 'Selecciona un nivel válido.',
            'status.in' => 'Selecciona un estado válido.',
        ];
    }
}
