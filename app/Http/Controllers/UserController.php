<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();

        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in(['owner', 'staff'])],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
        ]);

        ActivityLogger::log(
            'user.created',
            "Admin baru ditambahkan: {$user->name} ({$user->role})",
            User::class,
            $user->id,
        );

        return redirect()->route('users.index')->with('success', 'Pengguna admin berhasil ditambahkan.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus akun Anda sendiri.');
        }

        if (User::count() <= 1) {
            return redirect()->back()->with('error', 'Minimal harus ada satu akun admin.');
        }

        $name = $user->name;
        $user->delete();

        ActivityLogger::log('user.deleted', "Admin dihapus: {$name}");

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
