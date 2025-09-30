<?php

namespace App\Http\Services;

use App\Models\User;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(private ViewFactory $view) {}

    /** Query + paginação + render do partial para o fetch AJAX */
    public function fetch(string $q, int $page, int $perPage): array
    {
        $query = User::query()
            ->when($q !== '', fn ($w) =>
                $w->where(function ($x) use ($q) {
                    $like = "%{$q}%";
                    $x->where('name', 'like', $like)
                      ->orWhere('code', 'like', $like);
                })
            )
            ->orderBy('name');

        $total = (clone $query)->count();
        $items = $query->forPage($page, $perPage)->get();

        $html = $this->view->make('admin.users._rows', compact('items'))->render();

        return [
            'html'    => $html,
            'page'    => $page,
            'perPage' => $perPage,
            'total'   => $total,
            'hasPrev' => $page > 1,
            'hasNext' => ($page * $perPage) < $total,
        ];
    }

    /** Regras de criação (hash de senha, email sintético, etc.) */
    public function create(array $data): User
    {
        return User::create([
            'code'     => $data['code'],
            'name'     => $data['name'],
            'type'     => $data['type'],
            'email'    => $data['code'].'@local',
            'password' => Hash::make($data['password']),
            'store_mode' => $data['store_mode'] ?? null,
        ]);
    }

    /** Atualização com regra de senha/admin */
    public function update(User $user, array $data): void
    {
        $payload = Arr::only($data, ['name','type','store_mode']);

        // Opcional: não permitir trocar senha de contas admin por aqui
        if (!empty($data['password']) && $user->type !== 'admin') {
            $payload['password'] = Hash::make($data['password']);
        }

        $user->update($payload);
    }

    /** Arquivar (soft delete) */
    public function archive(User $user): void
    {
        if (!$user->trashed()) {
            $user->delete();
        }
    }

    /** Reset de senha */
    public function resetPassword(User $user, string $password): void
    {
        $user->update(['password' => Hash::make($password)]);
    }

    /**
     * Normaliza a query de busca:
     * - força string
     * - remove espaços duplicados e controla whitespace
     * - remove chars de controle
     * - limita tamanho para evitar abusos (ex.: 120)
     */
    public function qTrim(mixed $q): string
    {
        if ($q === null) {
            return '';
        }

        $q = (string) $q;

        // remove caracteres de controle (exceto \n\t se preferir manter)
        $q = preg_replace('/[[:cntrl:]]+/u', '', $q) ?? $q;

        // normaliza espaços (qualquer whitespace → espaço simples)
        $q = preg_replace('/\s+/u', ' ', $q) ?? $q;

        $q = trim($q);

        // limite defensivo
        if (function_exists('mb_substr')) {
            $q = mb_substr($q, 0, 120);
        } else {
            $q = substr($q, 0, 120);
        }

        return $q;
    }
}
