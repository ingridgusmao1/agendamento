<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    public function store(Request $request)
    {
        // Aceita JSON ou multipart
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
            // Fotos opcionais
            'customer_photo'  => 'nullable|image|max:5120',
            'place_photo'     => 'nullable|image|max:5120',
        ]);

        $customer = Customer::create($validated);

        // Avatar (opcional)
        if ($request->hasFile('customer_photo')) {
            $filename = $this->customerFilename($customer->name, (int)$customer->id);
            $path     = $request->file('customer_photo')
                                ->storeAs('customers', $filename, 'public');
            $customer->avatar_path = $path;          // ex.: customers/JOSE-...-25.jpg
        }

        // Place (opcional) — usa lat/lng do request ou do próprio registro
        if ($request->hasFile('place_photo')) {
            $lat      = (float)($validated['lat'] ?? $customer->lat ?? 0);
            $lng      = (float)($validated['lng'] ?? $customer->lng ?? 0);
            $filename = $this->placeFilename($lat, $lng, (int)$customer->id);
            $path     = $request->file('place_photo')
                                ->storeAs('places', $filename, 'public');
            $customer->place_path = $path;           // ex.: places/M34D8732-P12D4301-25.jpg
        }

        $customer->save();

        // Flutter espera { id } simples
        return response()->json(['id' => $customer->id], 201);
    }

    // ----------------- Helpers de nome -----------------

    private function customerFilename(string $name, int $id): string
    {
        $slug = strtoupper(Str::slug($name, '-'));
        return "{$slug}-{$id}.jpg";
    }

    private function coordToken(float $v): string
    {
        $sign = $v < 0 ? 'M' : 'P';
        $abs  = abs($v);
        [$i, $d] = explode('.', sprintf('%.4f', $abs)); // 4 casas
        return "{$sign}{$i}D{$d}";
    }

    private function placeFilename(float $lat, float $lng, int $id): string
    {
        return $this->coordToken($lat).'-'.$this->coordToken($lng)."-{$id}.jpg";
    }
}
