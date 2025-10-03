<?php

namespace App\Http\Services;

use App\Models\Customer;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CustomerService
{
    public function __construct(private ViewFactory $view) {}

    /** ----------------------------------------------------------------
     *  LISTAGEM AJAX (busca com paginação)
     *  Compatível com:
     *    - fetch(Request $request)
     *    - fetch(string $q, int $page, int $perPage)
     *  ---------------------------------------------------------------- */
    public function fetch(Request|string $req, ?int $page = null, ?int $perPage = null): array
    {
      if ($req instanceof Request) {
          $q       = trim((string) $req->input('q', ''));
          $page    = max(1, (int) ($req->input('page') ?? 1));
          $perPage = max(1, (int) ($req->input('per_page') ?? $req->input('perPage', 10)));
      } else {
          $q       = trim((string) $req);
          $page    = max(1, (int) ($page ?? 1));
          $perPage = max(1, (int) ($perPage ?? 10));
      }

      $query = Customer::query();
      if ($q !== '') {
          $query->where(function ($w) use ($q) {
              $w->where('name', 'like', "%{$q}%")
                ->orWhere('cpf', 'like', "%{$q}%")
                ->orWhere('phone', 'like', "%{$q}%")
                ->orWhere('city', 'like', "%{$q}%")
                ->orWhere('district', 'like', "%{$q}%");
          });
      }

      $total = (clone $query)->count();
      $items = $query->orderBy('name')
                     ->forPage($page, $perPage)
                     ->get();

      $html = $this->view->make('admin.customers._rows', ['items' => $items])->render();

      $hasMore = $total > ($page * $perPage);
      return [
          'html'     => $html,
          'total'    => $total,
          'page'     => $page,
          'perPage'  => $perPage,
          'hasMore'  => $hasMore,
          'hasPrev'  => $page > 1,
          'hasNext'  => $hasMore,
      ];
    }

    /** ----------------------------------------------------------------
     *  CRIAÇÃO
     *  ---------------------------------------------------------------- */
    public function store(Request $request): Customer
    {
        return DB::transaction(function () use ($request) {
            $customer = new Customer();
            $this->fillFromRequest($customer, $request);
            $customer->save(); // precisa do ID para nomear arquivo

            if ($request->hasFile('avatar')) {
                $path = $this->storeAvatarFile($customer, $request->file('avatar'));
                $customer->avatar_path = $path;
                $customer->save();
            }

            return $customer;
        });
    }

    /** ----------------------------------------------------------------
     *  ATUALIZAÇÃO
     *  ---------------------------------------------------------------- */
    public function update(Customer $customer, Request $request): Customer
    {
        return DB::transaction(function () use ($customer, $request) {
            $oldName       = (string) $customer->name;
            $oldAvatarPath = (string) ($customer->avatar_path ?? '');

            $this->fillFromRequest($customer, $request);
            $customer->save();

            if ($request->hasFile('avatar')) {
                $this->deleteIfExists($oldAvatarPath);
                $newPath = $this->storeAvatarFile($customer, $request->file('avatar'));
                $customer->avatar_path = $newPath;
                $customer->save();
            } else {
                // Mesmo sem upload novo, padroniza nome de arquivo:
                //  - se o nome mudou OU
                //  - se o caminho atual não bate com o esperado (migração do padrão antigo)
                if ($oldAvatarPath) {
                    $ext = pathinfo($oldAvatarPath, PATHINFO_EXTENSION) ?: 'jpg';
                    $expected = $this->expectedAvatarPath($customer, $ext);
                    if ($oldName !== $customer->name || $expected !== $oldAvatarPath) {
                        $this->renameAvatarPath($customer, $oldAvatarPath, $expected);
                    }
                }
            }

            return $customer;
        });
    }

    /** Copia campos simples do Request para o model */
    private function fillFromRequest(Customer $c, Request $r): void
    {
        $c->name            = $r->input('name',            $c->name);
        $c->street          = $r->input('street',          $c->street);
        $c->number          = $r->input('number',          $c->number);
        $c->district        = $r->input('district',        $c->district);
        $c->city            = $r->input('city',            $c->city);
        $c->reference_point = $r->input('reference_point', $c->reference_point);
        $c->rg              = $r->input('rg',              $c->rg);
        $c->cpf             = $r->input('cpf',             $c->cpf);
        $c->phone           = $r->input('phone',           $c->phone);
        $c->other_contact   = $r->input('other_contact',   $c->other_contact);
        $c->lat             = $r->input('lat',             $c->lat);
        $c->lng             = $r->input('lng',             $c->lng);
    }

    /** ========= Nome esperado: CLIENTE-NOME-COM-HIFENS-EM-CAIXA-ALTA-ID.ext ========= */
    private function expectedAvatarPath(Customer $customer, string $ext): string
    {
        $dir   = 'customers';
        $base  = Str::slug((string) $customer->name, '-'); // ana-claudia
        $base  = strtoupper($base);                        // ANA-CLAUDIA
        if ($base === '') $base = 'CLIENTE';
        return $dir . '/' . sprintf('%s-%s.%s', $base, $customer->id, strtolower($ext));
    }

    /** Salva avatar com o padrão de nome atualizado (hífens + ID) */
    private function storeAvatarFile(Customer $customer, UploadedFile $file): string
    {
        $disk = Storage::disk('public');
        $dir  = 'customers';

        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $path = $this->expectedAvatarPath($customer, $ext);

        if (!$disk->exists($dir)) {
            $disk->makeDirectory($dir);
        }
        // putFileAs exige nome do arquivo apenas; vamos extraí-lo do $path
        $filename = basename($path);
        $disk->putFileAs($dir, $file, $filename);

        return $path; // compatível com asset('storage/'.$path)
    }

    /** Move arquivo atual para o caminho esperado */
    private function renameAvatarPath(Customer $customer, string $currentPath, string $expectedPath): void
    {
        $disk = Storage::disk('public');
        if (!$currentPath || $currentPath === $expectedPath) return;

        if ($disk->exists($currentPath)) {
            $disk->makeDirectory(dirname($expectedPath));
            $disk->move($currentPath, $expectedPath);
            $customer->avatar_path = $expectedPath;
            $customer->save();
        } else {
            // Arquivo antigo não existe; apenas ajusta o caminho salvo (evita ficar com referência quebrada)
            $customer->avatar_path = $expectedPath;
            $customer->save();
        }
    }

    /** Apaga arquivo anterior, se existir */
    private function deleteIfExists(?string $path): void
    {
        if (!$path) return;
        $disk = Storage::disk('public');
        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }
}
