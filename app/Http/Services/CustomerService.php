<?php

namespace App\Http\Services;

use App\Models\Customer;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CustomerService
{
    public function __construct(private ViewFactory $view) {}

    /** Listagem com busca e paginação, retorna HTML das linhas */
    public function fetch(string $q, int $page, int $perPage): array
    {
        $query = Customer::query();

        if ($q !== '') {
            $like = '%' . str_replace(['%','_'], ['\\%','\\_'], $q) . '%';
            $query->where(function ($w) use ($like) {
                $w->where('name', 'like', $like)
                  ->orWhere('cpf', 'like', $like)
                  ->orWhere('rg', 'like', $like)
                  ->orWhere('phone', 'like', $like)
                  ->orWhere('city', 'like', $like)
                  ->orWhere('district', 'like', $like)
                  ->orWhere('street', 'like', $like);
            });
        }

        $total  = (clone $query)->count();
        $items  = $query->orderBy('name')->forPage($page, $perPage)->get();

        $html = $this->view->make('admin.customers._rows', ['items' => $items])->render();

        $hasMore = $total > ($page * $perPage);

        return [
            'html'    => $html,
            'total'   => $total,
            'page'    => $page,
            'perPage' => $perPage,
            'hasMore' => $hasMore,
        ];
    }

    public function store(Request $request): Customer
    {
        return DB::transaction(function () use ($request) {
            $data = $request->only([
                'name','street','number','district','city','reference_point',
                'rg','cpf','phone','other_contact','lat','lng',
            ]);

            /** @var Customer $customer */
            $customer = Customer::create($data);

            // avatar (opcional, único)
            if ($request->hasFile('avatar')) {
                $path = $this->saveAvatar($customer, $request->file('avatar'));
                $customer->avatar_path = $path;
                $customer->save();
            }

            return $customer;
        });
    }

    public function update(Customer $customer, Request $request): Customer
    {
        return DB::transaction(function () use ($customer, $request) {
            $data = $request->only([
                'name','street','number','district','city','reference_point',
                'rg','cpf','phone','other_contact','lat','lng',
            ]);
            $customer->fill($data)->save();

            // substituir avatar, se enviado
            if ($request->hasFile('avatar')) {
                // apaga antigo, se houver
                if ($customer->avatar_path) {
                    @unlink(public_path($customer->avatar_path));
                }
                $path = $this->saveAvatar($customer, $request->file('avatar'));
                $customer->avatar_path = $path;
                $customer->save();
            }

            return $customer;
        });
    }

    public function destroy(Customer $customer): void
    {
        // remove avatar se houver
        if ($customer->avatar_path) {
            @unlink(public_path($customer->avatar_path));
        }
        $customer->delete();
    }

    private function saveAvatar(Customer $customer, \Illuminate\Http\UploadedFile $file): string
    {
        $dir = public_path('customers');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $ext   = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $slug  = \Illuminate\Support\Str::slug((string) $customer->name) ?: 'cliente';
        $fname = sprintf('%s-%s.%s', strtoupper($slug), $customer->id, $ext);

        // grava em public/customers
        $file->move($dir, $fname);

        // caminho relativo para servir via /customers/...
        return 'customers/'.$fname;
    }
}
