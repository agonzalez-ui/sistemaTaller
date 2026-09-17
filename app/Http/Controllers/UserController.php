<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveUserRequest;
use App\Models\Role;
use App\Models\User;
use App\Support\SecurityAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:150'], 'status' => ['nullable', 'in:active,inactive'], 'role' => ['nullable', 'integer', 'exists:roles,id']]);
        $users = User::with('role')
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%")
                        ->orWhere('username', 'like', "%$search%")
                        ->orWhere('identification_number', 'like', "%$search%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('active', $status === 'active'))
            ->when($filters['role'] ?? null, fn ($query, $role) => $query->where('role_id', $role))
            ->orderBy('name')->paginate(10)->withQueryString();

        return view('users.index', ['users' => $users, 'roles' => Role::orderBy('name')->get()]);
    }

    public function create(): View
    {
        return view('users.create', ['roles' => Role::where('active', true)->orderBy('name')->get()]);
    }

    public function store(SaveUserRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $data = $request->validated();
            $verified = (bool) $data['verified'];
            unset($data['verified']);
            $user = new User;
            $user->forceFill($data + ['email_verified_at' => $verified ? now() : null])->save();
            SecurityAudit::record($request, 'INSERT', 'users', $user->id, 'Usuario creado con rol y estado asignados.');
        });

        return redirect()->route('users.index')->with('success', 'Usuario registrado correctamente.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', ['user' => $user, 'roles' => Role::where('active', true)->orderBy('name')->get()]);
    }

    public function update(SaveUserRequest $request, User $user): RedirectResponse
    {
        DB::transaction(function () use ($request, $user) {
            $data = $request->validated();
            $this->protectAdministrator($request, $user, (int) $data['role_id'], (bool) $data['active']);
            $verified = (bool) $data['verified'];
            unset($data['verified']);
            if (empty($data['password'])) {
                unset($data['password']);
            } else {
                $data['remember_token'] = Str::random(60);
                if (config('session.driver') === 'database') {
                    DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)
                        ->where('id', '!=', $request->session()->getId())->delete();
                }
            }
            $data['email_verified_at'] = $verified ? ($user->email === $data['email'] ? ($user->email_verified_at ?? now()) : now()) : null;
            $user->forceFill($data)->save();
            SecurityAudit::record($request, 'UPDATE', 'users', $user->id, 'Perfil, rol o estado del usuario actualizado.');
        });

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        DB::transaction(function () use ($request, $user) {
            $this->protectAdministrator($request, $user, (int) $user->role_id, false);
            $user->forceFill(['active' => false, 'remember_token' => Str::random(60)])->save();
            SecurityAudit::record($request, 'DISABLE', 'users', $user->id, 'Usuario deshabilitado; historial conservado.');
        });

        return redirect()->route('users.index')->with('success', 'Usuario deshabilitado correctamente.');
    }

    private function protectAdministrator(Request $request, User $user, int $roleId, bool $active): void
    {
        $administrators = User::where('active', true)->whereHas('role', fn ($query) => $query->where('active', true)->where('is_administrator', true))->lockForUpdate()->get();
        if ($request->user()->id === $user->id && (! $active || $roleId !== $user->role_id)) {
            throw ValidationException::withMessages(['active' => 'No puede deshabilitar su propia cuenta ni cambiar su propio rol.']);
        }
        $nextRole = Role::find($roleId);
        if ($administrators->contains('id', $user->id) && (! $active || ! $nextRole?->is_administrator) && $administrators->count() <= 1) {
            throw ValidationException::withMessages(['role_id' => 'Debe conservar al menos un administrador activo.']);
        }
    }
}
