<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\UserManagement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'role_id' => ['nullable', 'integer', Rule::exists('roles', 'role_id')],
            'account_status' => ['nullable', Rule::in(User::STATUSES)],
        ]);
        $users = User::query()->with('role')
            ->when($request->filled('q'), fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', '%'.$filters['q'].'%')->orWhere('email', 'like', '%'.$filters['q'].'%')))
            ->when($filters['role_id'] ?? null, fn ($query, $id) => $query->where('role_id', $id))
            ->when($filters['account_status'] ?? null, fn ($query, $status) => $query->where('account_status', $status))
            ->orderBy('name')->orderBy('id')->paginate(15)->withQueryString();

        return view('users.index', ['users' => $users, 'roles' => Role::orderBy('role_name')->get(), 'filters' => $filters]);
    }

    public function create(): View
    {
        return view('users.form', ['record' => new User, 'roles' => Role::orderBy('role_name')->get()]);
    }

    public function store(UserRequest $request, UserManagement $management): RedirectResponse
    {
        $user = $management->create($request->user(), $request->validated());

        return redirect()->route('manage.users.show', $user)->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function show(User $user): View
    {
        return view('users.show', ['record' => $user->load('role')]);
    }

    public function edit(User $user): View
    {
        return view('users.form', ['record' => $user, 'roles' => Role::orderBy('role_name')->get()]);
    }

    public function update(UserRequest $request, User $user, UserManagement $management): RedirectResponse
    {
        $updated = $management->update($request->user(), $user, $request->validated());

        if ($updated->id === $request->user()->id && ! $updated->load('role')->canManageSupportingData()) {
            return redirect()->route('account')->with('success', 'Data pengguna berhasil diperbarui. Peran Anda kini mengikuti hak akses yang dipilih.');
        }

        return redirect()->route('manage.users.show', $user)->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function status(Request $request, User $user, UserManagement $management): RedirectResponse
    {
        $data = $request->validate(['account_status' => ['required', Rule::in(User::STATUSES)]]);
        $management->update($request->user(), $user, $data);

        return redirect()->route('manage.users.show', $user)->with('success', 'Status akun berhasil diperbarui.');
    }
}
