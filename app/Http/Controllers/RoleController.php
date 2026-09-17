<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveRoleRequest;
use App\Models\Module;
use App\Models\Role;
use App\Support\SecurityAudit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:100'], 'status' => ['nullable', 'in:active,inactive']]);
        $roles = Role::withCount('users')
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(fn ($query) => $query->where('name', 'like', "%$search%")->orWhere('description', 'like', "%$search%")))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('active', $status === 'active'))
            ->orderByDesc('is_administrator')->orderBy('name')->paginate(10)->withQueryString();

        return view('roles.index', compact('roles'));
    }

    public function create(): View
    {
        return view('roles.create', ['modules' => $this->editableModules()->get()]);
    }

    public function store(SaveRoleRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $role = Role::create($request->safe()->only(['name', 'description', 'active']));
            $this->savePermissions($request, $role);
            SecurityAudit::record($request, 'INSERT', 'roles', $role->id, 'Rol creado con permisos por módulo.');
        });

        return redirect()->route('roles.index')->with('success', 'Rol registrado correctamente.');
    }

    public function edit(Role $role): View
    {
        $role->load('modules');

        return view('roles.edit', ['role' => $role, 'modules' => $this->editableModules()->get()]);
    }

    public function update(SaveRoleRequest $request, Role $role): RedirectResponse
    {
        if ($role->is_administrator && ! $request->boolean('active')) {
            throw ValidationException::withMessages(['active' => 'El rol administrador debe permanecer activo.']);
        }
        DB::transaction(function () use ($request, $role) {
            $role->update($request->safe()->only(['name', 'description', 'active']));
            if (! $role->is_administrator) {
                $this->savePermissions($request, $role);
            }
            SecurityAudit::record($request, 'UPDATE', 'roles', $role->id, 'Datos, estado o permisos del rol actualizados.');
        });

        return redirect()->route('roles.index')->with('success', 'Rol actualizado correctamente.');
    }

    public function destroy(Request $request, Role $role): RedirectResponse
    {
        if ($role->is_administrator) {
            throw ValidationException::withMessages(['active' => 'El rol administrador no puede deshabilitarse.']);
        }
        DB::transaction(function () use ($request, $role) {
            $role->update(['active' => false]);
            SecurityAudit::record($request, 'DISABLE', 'roles', $role->id, 'Rol deshabilitado; sus usuarios pierden acceso al sistema.');
        });

        return redirect()->route('roles.index')->with('success', 'Rol deshabilitado correctamente.');
    }

    private function editableModules(): Builder
    {
        return Module::where('active', true)->whereNotIn('slug', ['roles', 'users', 'admin_users'])->orderBy('sort_order');
    }

    private function savePermissions(SaveRoleRequest $request, Role $role): void
    {
        $submitted = $request->validated('permissions', []);
        $permissions = [];
        foreach ($this->editableModules()->get() as $module) {
            $values = $submitted[$module->id] ?? [];
            $view = (bool) ($values['can_view'] ?? false);
            $permissions[$module->id] = [
                'can_view' => $view,
                'can_create' => $view && (bool) ($values['can_create'] ?? false),
                'can_edit' => $view && (bool) ($values['can_edit'] ?? false),
                'can_delete' => $view && (bool) ($values['can_delete'] ?? false),
            ];
        }
        $role->modules()->sync($permissions);
    }
}
