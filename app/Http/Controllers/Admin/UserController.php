<?php

namespace App\Http\Controllers\Admin;

use App\Actions\RecordActivity;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Desa;
use App\Models\User;
use App\UserRole;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);

        $users = User::query()
            ->with('desas.kecamatan')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = '%'.$request->string('search')->trim()->value().'%';
                $query->where(function ($users) use ($search): void {
                    $users->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhere('username', 'like', $search);
                });
            })
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->string('role')->value()))
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => UserRole::cases(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        Gate::authorize('create', User::class);

        return view('admin.users.create', [
            'roles' => UserRole::cases(),
            'desas' => Desa::query()->with('kecamatan')->orderBy('nama')->orderBy('id')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request, RecordActivity $recordActivity): RedirectResponse
    {
        $validated = $request->validated();

        $user = DB::transaction(function () use ($validated): User {
            $user = User::query()->create(Arr::except($validated, ['desa_ids']));
            $this->syncDesaAssignments($user, $validated['desa_ids'] ?? []);

            return $user;
        });

        $recordActivity->execute('user.created', $user, ['role' => $user->role->value], $request->user(), $request->ip());

        return redirect()->route('admin.users.index')->with('status', 'Akun berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        Gate::authorize('update', $user);
        $user->load('desas');

        return view('admin.users.edit', [
            'managedUser' => $user,
            'roles' => UserRole::cases(),
            'desas' => Desa::query()->with('kecamatan')->orderBy('nama')->orderBy('id')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user, RecordActivity $recordActivity): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->user()?->is($user)
            && ($validated['role'] !== UserRole::SuperAdmin->value || ! $validated['is_active'])) {
            throw ValidationException::withMessages([
                'role' => 'Anda tidak dapat menurunkan role atau menonaktifkan akun sendiri.',
            ]);
        }

        $changes = DB::transaction(function () use ($validated, $user): array {
            $userData = Arr::except($validated, ['desa_ids']);

            if (blank($userData['password'] ?? null)) {
                unset($userData['password']);
            }

            $user->fill($userData);
            $changed = array_keys($user->getDirty());
            $user->save();
            $this->syncDesaAssignments($user, $validated['desa_ids'] ?? []);
            $detachedKawasanIds = $this->detachInvalidKawasanAssignments($user);

            return ['changed_fields' => $changed, 'detached_kawasan_ids' => $detachedKawasanIds];
        });

        $recordActivity->execute('user.updated', $user, ['changed_fields' => $changes['changed_fields']], $request->user(), $request->ip());

        if ($changes['detached_kawasan_ids'] !== []) {
            $recordActivity->execute('kawasan.admins.detached_by_role_change', $user, [
                'kawasan_ids' => $changes['detached_kawasan_ids'],
            ], $request->user(), $request->ip());
        }

        return redirect()->route('admin.users.index')->with('status', 'Akun berhasil diperbarui.');
    }

    /** @param array<int, int|string> $desaIds */
    private function syncDesaAssignments(User $user, array $desaIds): void
    {
        $user->desas()->sync($user->role === UserRole::AdminDesa ? $desaIds : []);
    }

    /** @return array<int, int> */
    private function detachInvalidKawasanAssignments(User $user): array
    {
        if ($user->role === UserRole::AdminKawasan) {
            return [];
        }

        $assignedKawasanIds = $user->kawasans()->pluck('kawasan_perdesaan.id')->all();
        $user->kawasans()->detach();

        return $assignedKawasanIds;
    }
}
