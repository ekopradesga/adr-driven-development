<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\Identity\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        return view('users.index', $this->userService->buildIndexData(
            $request->only('search', 'status')
        ));
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('users.create', $this->userService->buildCreateData());
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = $this->userService->create($request->validated());

        if ($request->filled('roles')) {
            $this->userService->syncRoles($user, $request->input('roles'));
        }

        return redirect()->route('users.show', $user)
            ->with('success', 'User created successfully.');
    }

    public function show(User $user): View
    {
        $this->authorize('view', $user);

        return view('users.show', $this->userService->findForShow($user));
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('users.edit', $this->userService->buildEditData($user));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->userService->update($user, $request->validated());

        if ($request->has('roles')) {
            $this->userService->syncRoles($user, $request->input('roles', []));
        }

        return redirect()->route('users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    public function suspend(User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        if (auth()->id() === $user->id) {
            return back()->with('error', 'You cannot suspend your own account.');
        }

        $this->userService->suspend($user);

        return back()->with('success', "User {$user->name} suspended.");
    }

    public function activate(User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $this->userService->activate($user);

        return back()->with('success', "User {$user->name} activated.");
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        if (auth()->id() === $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $this->userService->softDelete($user);

        return redirect()->route('users.index')
            ->with('success', 'User deleted.');
    }
}
