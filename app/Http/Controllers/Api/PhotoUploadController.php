<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Photo;
use App\Models\Customer;
use App\Http\Services\PhotoPathService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PhotoUploadController extends Controller
{
public function store(Request $request)
    {
        // validação básica
        $data = $request->validate([
            'kind'       => ['required', Rule::in(['cliente','lugar'])],
            'owner_id'   => ['required','integer','min:1'],
            // owner_name só é exigido para cliente
            'owner_name' => ['nullable','string','min:1'],
            // lat/lng opcionais (usados se kind=lugar)
            'lat'        => ['nullable','numeric'],
            'lng'        => ['nullable','numeric'],
            'index'      => ['nullable','integer','min:1'],
            'file'       => ['required','file','mimes:jpg,jpeg,png,webp','max:5120'],
        ]);

        $kind  = $data['kind'];
        $id    = (int) $data['owner_id'];
        $index = $data['index'] ?? 1;

        // ===== Nome base dependendo do kind =====
        if ($kind === 'cliente') {
            // usa owner_name informado (obrigatório de fato para cliente)
            $ownerName = $data['owner_name'] ?? null;
            if (!$ownerName) {
                // tentar resolver pelo próprio Customer
                $c = Customer::find($id);
                if (!$c) {
                    return response()->json(['message' => 'Cliente não encontrado para owner_id'], 422);
                }
                $ownerName = $c->name;
            }
            $displayName = $ownerName;

        } else { // kind === 'lugar'
            // prioridade: lat/lng do payload; se não vier, buscar do Customer
            $lat = $data['lat'] ?? null;
            $lng = $data['lng'] ?? null;

            if ($lat === null || $lng === null) {
                $c = Customer::find($id);
                if (!$c || $c->lat === null || $c->lng === null) {
                    return response()->json(['message' => 'Lat/Lng ausentes no payload e não encontrados no Customer'], 422);
                }
                $lat = $c->lat;
                $lng = $c->lng;
            }
            $displayName = PhotoPathService::placeNameFromLatLng((float)$lat, (float)$lng);
        }

        $path = PhotoPathService::saveUploaded(
            $kind,
            $displayName,
            $id,
            $request->file('file'),
            $index
        );

        $photo = Photo::create([
            'kind' => $kind,
            'path' => $path,
        ]);

        return response()->json([
            'id'   => $photo->id,
            'path' => $photo->path,
            'url'  => $photo->url,
        ], 201);
    }
}
