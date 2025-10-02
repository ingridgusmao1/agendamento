<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function store(Request $request)
    {
        // Aceita JSON ou multipart (Dio FormData)
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'street'          => 'nullable|string|max:255',
            'number'          => 'nullable|string|max:50',
            'district'        => 'nullable|string|max:255',
            'city'            => 'nullable|string|max:255',
            'reference_point' => 'nullable|string|max:255',
            'rg'              => 'nullable|string|max:20',
            'cpf'             => 'nullable|string|max:20',
            'phone'           => 'nullable|string|max:20',
            'other_contact'   => 'nullable|string|max:255',
            'lat'             => 'nullable|numeric',
            'lng'             => 'nullable|numeric',

            // fotos opcionais (o app pode ou não enviar)
            'customer_photo'  => 'nullable|file|image|max:4096',
            'place_photo'     => 'nullable|file|image|max:4096',
        ]);

        $customer = Customer::create($validated);

        // Se vier foto do cliente → salva e atualiza avatar_path
        if ($request->hasFile('customer_photo')) {
            $path = $request->file('customer_photo')->store('customers', 'public');
            $customer->avatar_path = $path; // ex.: customers/abc.jpg
        }

        // Se vier foto do lugar → salva com padrão M/P + D + id e atualiza place_path
        if ($request->hasFile('place_photo')) {
            // Usa lat/lng do request (se enviados) ou do próprio customer recém-criado
            $lat = (float)($validated['lat'] ?? $customer->lat ?? 0);
            $lng = (float)($validated['lng'] ?? $customer->lng ?? 0);

            $filename = $this->placeFilename($lat, $lng, (int)$customer->id);
            $path = $request->file('place_photo')->storeAs('places', $filename, 'public');
            $customer->place_path = $path; // ex.: places/M34D8732-P12D4301-25.jpg
        }

        $customer->save();

        // O app espera { id } simples na criação do cliente. :contentReference[oaicite:0]{index=0}
        return response()->json(['id' => $customer->id], 201);
    }

    private function coordToken(float $v): string
    {
        $sign = $v < 0 ? 'M' : 'P';
        $abs  = abs($v);
        $formatted = sprintf('%.4f', $abs); // 4 casas decimais
        [$i, $d] = explode('.', $formatted);
        return "{$sign}{$i}D{$d}";
    }

    private function placeFilename(float $lat, float $lng, int $id): string
    {
        return $this->coordToken($lat) . '-' . $this->coordToken($lng) . "-{$id}.jpg";
    }
}
