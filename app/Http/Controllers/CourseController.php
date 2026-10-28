<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourseRequest;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $courses = Course::with('user')->latest()->get();

        return view('courses.index', compact('courses'));
    }

    public function create(): View
    {
        return view('courses.create');
    }

    public function store(CourseRequest $request): RedirectResponse
    {
        $request->user()->courses()->create($request->validated());

        return redirect()->route('cursos.index')
            ->with('success', 'Curso creado correctamente.');
    }

    public function show(Request $request, Course $course): View
    {
        return view('courses.show', compact('course'));
    }

    public function edit(Request $request, Course $course): View
    {
        $this->ensureCanManage($request, $course);

        return view('courses.edit', compact('course'));
    }

    public function update(CourseRequest $request, Course $course): RedirectResponse
    {
        $this->ensureCanManage($request, $course);
        $course->update($request->validated());

        return redirect()->route('cursos.index')
            ->with('success', 'Curso actualizado correctamente.');
    }

    public function destroy(Request $request, Course $course): RedirectResponse
    {
        $this->ensureCanManage($request, $course);
        $course->delete();

        return redirect()->route('cursos.index')
            ->with('success', 'Curso eliminado correctamente.');
    }

    private function ensureCanManage(Request $request, Course $course): void
    {
        $user = $request->user();

        abort_unless($user->isAdmin() || ($user->isDocente() && $course->user_id === $user->id), 403);
    }
}
