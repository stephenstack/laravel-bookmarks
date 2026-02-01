<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Inertia\Inertia;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->select(['id', 'name', 'email', 'is_admin', 'created_at'])
            ->addSelect([
                'last_login_at' => DB::table('sessions')
                    ->selectRaw('MAX(last_activity)')
                    ->whereColumn('user_id', 'users.id'),
            ])
            ->orderBy('name')
            ->get()
            ->map(function (User $user) {
                $lastLogin = $user->last_login_at ? Carbon::createFromTimestamp((int) $user->last_login_at) : null;

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_admin' => $user->is_admin,
                    'created_at' => $user->created_at?->toIso8601String(),
                    'last_login_at' => $lastLogin?->toIso8601String(),
                ];
            });

        return Inertia::render('Admin/Users', [
            'users' => $users,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'is_admin' => ['sometimes', 'boolean'],
            'password' => ['sometimes', 'nullable', 'confirmed', PasswordRule::defaults()],
        ]);

        if (array_key_exists('is_admin', $data)) {
            $isAdmin = (bool) $data['is_admin'];
            if ($request->user()?->id === $user->id && !$isAdmin) {
                return back()->withErrors([
                    'is_admin' => 'You cannot remove your own admin access.',
                ]);
            }
            $user->is_admin = $isAdmin;
        }

        if (!empty($data['password'])) {
            $user->forceFill([
                'password' => $data['password'],
            ]);
        }

        if ($user->isDirty()) {
            $user->save();
        }

        return back();
    }

    public function invite(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $name = $data['name'] ?: Str::before($data['email'], '@');

        $user = User::create([
            'name' => $name,
            'email' => $data['email'],
            'password' => Str::random(32),
            'is_admin' => false,
        ]);

        Password::sendResetLink(['email' => $user->email]);

        return back();
    }
}
