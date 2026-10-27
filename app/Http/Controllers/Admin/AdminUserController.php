<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ciudad;
use App\Models\Pais;
use App\Models\Provincia;
use App\Models\User;
use App\Rules\ValidEcuadorianCedula;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    /**
     * Lista todos los usuarios registrados en el sistema.
     */
    public function index(): View
    {
        $usuarios = User::with(['pais', 'provincia', 'ciudad'])->latest()->paginate(15);

        return view('admin.usuarios.index', [
            'usuarios' => $usuarios,
        ]);
    }

    /**
     * Muestra el formulario de registro de usuario con dropdowns dependientes en cascada.
     */
    public function create(): View
    {
        $paises = Pais::orderBy('nombre')->get();

        return view('admin.usuarios.create', [
            'paises' => $paises,
        ]);
    }

    /**
     * Guarda el usuario tras realizar la VALIDACIÓN EN BACK-END DEL DATO SENSIBLE (Cédula de identidad).
     * No puede ser validado solo con JS, sino validado rigurosamente en back-end antes de grabar en BD.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'min:8'],
            'role' => ['required', 'string', 'in:estudiante,docente,admin'],

            // VALIDACIÓN BACK-END DE DATO SENSIBLE (Módulo 10 Registro Civil)
            'cedula' => [
                'required',
                'string',
                'unique:users,cedula',
                new ValidEcuadorianCedula(),
            ],

            // DROPDOWNS DEPENDIENTES: Claves foráneas validadas en BD
            'pais_id' => ['required', 'integer', 'exists:paises,id'],
            'provincia_id' => [
                'required',
                'integer',
                Rule::exists('provincias', 'id')->where(fn ($query) => $query->where('pais_id', $request->input('pais_id'))),
            ],
            'ciudad_id' => [
                'required',
                'integer',
                Rule::exists('ciudades', 'id')->where(fn ($query) => $query->where('provincia_id', $request->input('provincia_id'))),
            ],
        ], [
            'cedula.required' => 'La cédula es un dato sensible obligatorio en el sistema.',
            'cedula.unique' => 'Esta cédula ya se encuentra registrada por otro usuario.',
            'pais_id.required' => 'Debe seleccionar un país desde el dropdown.',
            'provincia_id.required' => 'Debe seleccionar una provincia válida para el país elegido.',
            'provincia_id.exists' => 'La provincia seleccionada no pertenece al país seleccionado.',
            'ciudad_id.required' => 'Debe seleccionar una ciudad válida para la provincia elegida.',
            'ciudad_id.exists' => 'La ciudad seleccionada no pertenece a la provincia seleccionada.',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'cedula' => $validated['cedula'],
            'pais_id' => $validated['pais_id'],
            'provincia_id' => $validated['provincia_id'],
            'ciudad_id' => $validated['ciudad_id'],
        ]);

        return redirect()->route('admin.usuarios.index')
            ->with('success', "Usuario '{$validated['name']}' registrado exitosamente con validación de cédula en Back-End.");
    }

    /**
     * Muestra el formulario de edición de usuario.
     */
    public function edit(User $usuario): View
    {
        $paises = Pais::orderBy('nombre')->get();
        $provincias = $usuario->pais_id ? Provincia::where('pais_id', $usuario->pais_id)->get() : collect();
        $ciudades = $usuario->provincia_id ? Ciudad::where('provincia_id', $usuario->provincia_id)->get() : collect();

        return view('admin.usuarios.edit', [
            'usuario' => $usuario,
            'paises' => $paises,
            'provincias' => $provincias,
            'ciudades' => $ciudades,
        ]);
    }

    /**
     * Actualiza un usuario con validación estricta de cédula y dropdowns dependientes.
     */
    public function update(Request $request, User $usuario): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($usuario->id)],
            'password' => ['nullable', 'min:8'],
            'role' => ['required', 'string', 'in:estudiante,docente,admin'],

            // VALIDACIÓN BACK-END DE DATO SENSIBLE
            'cedula' => [
                'required',
                'string',
                Rule::unique('users')->ignore($usuario->id),
                new ValidEcuadorianCedula(),
            ],

            // DROPDOWNS DEPENDIENTES
            'pais_id' => ['required', 'integer', 'exists:paises,id'],
            'provincia_id' => [
                'required',
                'integer',
                Rule::exists('provincias', 'id')->where(fn ($query) => $query->where('pais_id', $request->input('pais_id'))),
            ],
            'ciudad_id' => [
                'required',
                'integer',
                Rule::exists('ciudades', 'id')->where(fn ($query) => $query->where('provincia_id', $request->input('provincia_id'))),
            ],
        ], [
            'cedula.required' => 'La cédula es obligatoria.',
            'cedula.unique' => 'Esta cédula ya está en uso.',
            'provincia_id.exists' => 'La provincia no corresponde al país seleccionado.',
            'ciudad_id.exists' => 'La ciudad no corresponde a la provincia seleccionada.',
        ]);

        $usuario->name = $validated['name'];
        $usuario->email = $validated['email'];
        if (! empty($validated['password'])) {
            $usuario->password = Hash::make($validated['password']);
        }
        $usuario->role = $validated['role'];
        $usuario->cedula = $validated['cedula'];
        $usuario->pais_id = $validated['pais_id'];
        $usuario->provincia_id = $validated['provincia_id'];
        $usuario->ciudad_id = $validated['ciudad_id'];
        $usuario->save();

        return redirect()->route('admin.usuarios.index')
            ->with('success', "Usuario '{$usuario->name}' actualizado correctamente.");
    }

    /**
     * Elimina un usuario del sistema (excepto el usuario autenticado).
     */
    public function destroy(User $usuario): RedirectResponse
    {
        if ($usuario->id === auth()->id()) {
            return back()->withErrors(['No puedes eliminar tu propia cuenta en sesión.']);
        }

        $nombre = $usuario->name;
        $usuario->delete();

        return redirect()->route('admin.usuarios.index')
            ->with('success', "Usuario '{$nombre}' eliminado del sistema.");
    }
}
