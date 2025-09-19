<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Validators\UserValidator;
use App\Models\User;
use App\Http\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class UserAdminController extends Controller
{
    private const PER_PAGE = 15;

    public function __construct(private UserService $service) {}

    /** Página principal com tipos disponíveis para o <select> */
    public function index(Request $r)
    {
        $q = trim((string) $r->query('q', ''));
        $types = UserValidator::TYPES;

        return view('admin.users.index', compact('q','types'));
    }

    /** Endpoint AJAX para montar linhas da tabela */
    public function fetch(Request $r)
    {
        $r->validate(UserValidator::fetch());
        $q    = trim((string)$r->query('q',''));
        $page = max(1, (int)$r->query('page',1));

        return response()->json(
            $this->service->fetch($q, $page, self::PER_PAGE)
        );
    }

    /** Criar usuário */
    public function store(Request $r): RedirectResponse
    {
        $data = $r->validate(UserValidator::store());
        $this->service->create($data);

        return back()->with('ok', __('global.user_created'));
    }

    /** Atualizar usuário */
    public function update(Request $r, User $user): RedirectResponse
    {
        $data = $r->validate(UserValidator::update($user->id));
        $this->service->update($user, $data);

        return back()->with('ok', __('global.user_updated'));
    }

    /** Reset de senha */
    public function resetPassword(Request $r, User $user): RedirectResponse
    {
        $data = $r->validate(UserValidator::resetPassword());
        $this->service->resetPassword($user, $data['password']);

        return back()->with('ok', __('global.password_reset'));
    }

    /** Arquivar (soft delete) com proteções */
    public function destroy(User $user): RedirectResponse
    {
        // Não permitir apagar a si mesmo
        if (auth()->id() === $user->id) {
            return back()->withErrors(['error' => __('global.cannot_delete_self')]);
        }

        // Não permitir apagar contas admin
        if ($user->type === 'admin') {
            return back()->withErrors(['error' => __('global.cannot_delete_admin')]);
        }

        if ($user->trashed()) {
            return back()->with('ok', __('global.user_already_archived'));
        }

        $this->service->archive($user);
        return back()->with('ok', __('global.user_archived'));
    }
}
