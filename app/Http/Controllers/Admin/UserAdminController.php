<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;

class UserAdminController extends Controller
{
    public function index(Request $r)
    {
        $q = trim((string)$r->query('q', ''));
        $items = User::query()
            ->when($q !== '', fn($w) =>
                $w->where('name','like',"%$q%")
                  ->orWhere('code','like',"%$q%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $types = ['admin','vendedor','cobrador','vendedor_cobrador'];
        return view('admin.users.index', compact('items','q','types'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'code' => 'required|string|max:50|unique:users,code',
            'name' => 'required|string|max:120',
            'type' => 'required|in:admin,vendedor,cobrador,vendedor_cobrador',
            'password' => 'required|string|min:4',
        ]);

        User::create([
            'code' => $data['code'],
            'name' => $data['name'],
            'type' => $data['type'],
            'email' => $data['code'].'@local',
            'password' => Hash::make($data['password']),
        ]);

        return back()->with('ok', __('global.user_created'));
    }

    public function update(Request $r, User $user)
    {
        $data = $r->validate([
            'name' => 'required|string|max:120',
            'type' => 'required|in:admin,vendedor,cobrador,vendedor_cobrador',
        ]);

        $user->update($data);
        return back()->with('ok', __('global.user_updated'));
    }

    public function destroy(User $user): RedirectResponse
    {
        if (auth()->id() === $user->id) {
            return back()->withErrors(['error' => __('global.cannot_delete_self')]);
        }

        if ($user->trashed()) {
            return back()->with('ok', __('global.user_already_archived'));
        }

        $user->delete();
        return back()->with('ok', __('global.user_archived'));
    }

    public function resetPassword(Request $r, User $user)
    {
        $data = $r->validate(['password' => 'required|string|min:4']);
        $user->update(['password' => Hash::make($data['password'])]);
        return back()->with('ok', __('global.password_reset'));
    }
}
