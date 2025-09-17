<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;

class UserAdminController extends Controller
{
    // Defina aqui o tamanho da "página" do fetch AJAX (4 em 4, como combinamos)
    private const PER_PAGE = 15;

    public function index(Request $r)
    {
        // A view agora carrega linhas via AJAX; não precisa buscar $items aqui
        $q = trim((string) $r->query('q', ''));
        $types = ['admin','vendedor','cobrador','vendedor_cobrador'];

        return view('admin.users.index', compact('q','types'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'code'     => 'required|string|max:50|unique:users,code',
            'name'     => 'required|string|max:120',
            'type'     => 'required|in:admin,vendedor,cobrador,vendedor_cobrador',
            'password' => 'required|string|min:4',
        ]);

        User::create([
            'code'     => $data['code'],
            'name'     => $data['name'],
            'type'     => $data['type'],
            'email'    => $data['code'].'@local',
            'password' => Hash::make($data['password']),
        ]);

        return back()->with('ok', __('global.user_created'));
    }

    public function update(Request $r, User $user)
    {
        $data = $r->validate([
            'name'     => 'required|string|max:120',
            'type'     => 'required|in:admin,vendedor,cobrador,vendedor_cobrador',
            'password' => 'nullable|string|min:4',
        ]);

        $payload = Arr::only($data, ['name','type']);

        // Só atualiza senha se enviada e se o tipo final NÃO é admin
        if (!empty($data['password']) && $data['type'] !== 'admin') {
            $payload['password'] = Hash::make($data['password']);
        }

        $user->update($payload);

        return back()->with('ok', __('global.user_updated'));
    }

    public function destroy(User $user): RedirectResponse
    {
        if (auth()->id() === $user->id) {
            return back()->withErrors(['error' => __('global.cannot_delete_self')]);
        }

        // Opcional (recomendado): nunca permitir apagar/arquivar admin pelo endpoint
        if ($user->type === 'admin') {
            return back()->withErrors(['error' => __('global.cannot_delete_admin')]);
        }

        if ($user->trashed()) {
            return back()->with('ok', __('global.user_already_archived'));
        }

        $user->delete(); // soft delete
        return back()->with('ok', __('global.user_archived'));
    }

    public function resetPassword(Request $r, User $user)
    {
        $data = $r->validate(['password' => 'required|string|min:4']);
        $user->update(['password' => Hash::make($data['password'])]);
        return back()->with('ok', __('global.password_reset'));
    }

    public function fetch(Request $r)
    {
        $q       = trim((string) $r->query('q', ''));
        $page    = max(1, (int) $r->query('page', 1));
        $perPage = self::PER_PAGE;

        $query = User::query()
            ->when($q !== '', fn ($w) =>
                $w->where(function ($x) use ($q) {
                    $x->where('name', 'like', "%$q%")
                      ->orWhere('code', 'like', "%$q%");
                })
            )
            ->orderBy('name');

        $total = (clone $query)->count();
        $items = $query->forPage($page, $perPage)->get();

        $html = view('admin.users._rows', compact('items'))->render();

        return response()->json([
            'html'    => $html,
            'page'    => $page,
            'perPage' => $perPage,
            'total'   => $total,
            'hasPrev' => $page > 1,
            'hasNext' => ($page * $perPage) < $total,
        ]);
    }
}
