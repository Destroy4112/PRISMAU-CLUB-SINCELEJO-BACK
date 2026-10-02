<?php

namespace App\Services;

use App\Models\Bebida;
use App\Models\Comida;
use App\Models\Preinventario;
use App\Models\PreinventarioItem;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PreinventarioItemService
{
    public function validarItemsLote(array $request)
    {
        $rules = [
            'items' => 'required|array',
            'items.*.type' => 'required|in:comida,bebida',
            'items.*.id' => 'required|integer|min:1',
            'items.*.cantidad_default' => 'required|integer|min:0',
        ];

        $messages = [
            'items.required' => 'Debes enviar al menos un item.',
            'items.array' => 'Items debe ser un arreglo.',
            'items.*.type.required' => 'El tipo es obligatorio (comida o bebida).',
            'items.*.type.in' => 'El tipo debe ser comida o bebida.',
            'items.*.id.required' => 'El id del producto es obligatorio.',
            'items.*.id.integer' => 'El id del producto debe ser numérico.',
            'items.*.cantidad_default.required' => 'La cantidad_default es obligatoria.',
            'items.*.cantidad_default.integer' => 'La cantidad_default debe ser numérica.',
            'items.*.cantidad_default.min' => 'La cantidad_default no puede ser negativa.',
        ];

        $validator = Validator::make($request, $rules, $messages);

        if ($validator->fails()) {
            return [
                'status' => false,
                'errors' => $validator->errors()->all(),
            ];
        }

        return ['status' => true];
    }

    public function cargarCatalogoProductos($preinventarioId)
    {
        $detalle = PreinventarioItem::where('preinventario_id', $preinventarioId)->get()
            ->mapWithKeys(function ($it) {
                $key = $it->itemable_type . ':' . $it->itemable_id;
                return [$key => (int) $it->cantidad_default];
            });

        $comidas = Comida::select('id', 'comida as nombre', 'precio', 'imagen')->where('estado', 1)->get()
            ->map(function ($c) use ($detalle) {
                $key = 'comida:' . $c->id;
                $cantidad = $detalle->get($key, 0);

                return [
                    ...$c->toArray(),
                    'type' => 'comida',
                    'incluido' => $detalle->has($key),
                    'cantidad_default' => $cantidad,
                ];
            })->toArray();

        $bebidas = Bebida::select('id', 'bebida as nombre', 'precio', 'imagen')->where('estado', 1)->get()
            ->map(function ($b) use ($detalle) {
                $key = 'bebida:' . $b->id;
                $cantidad = $detalle->get($key, 0);

                return [
                    ...$b->toArray(),
                    'type' => 'bebida',
                    'incluido' => $detalle->has($key),
                    'cantidad_default' => $cantidad,
                ];
            })->toArray();

        return array_merge($comidas, $bebidas);
    }

    public function guardarDetalle(int $preinventarioId, $request)
    {
        $res = $this->validarItemsLote($request);
        if (!$res['status']) return $res;

        try {
            Preinventario::findOrFail($preinventarioId);

            return DB::transaction(function () use ($preinventarioId, $request) {
                $now = now();

                $rows = collect($request['items'])->map(function ($it) use ($preinventarioId, $now) {
                    return [
                        'preinventario_id' => $preinventarioId,
                        'itemable_type' => $it['type'],
                        'itemable_id' => (int) $it['id'],
                        'cantidad_default' => (int) $it['cantidad_default'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                })->toArray();

                if (!empty($rows)) {
                    PreinventarioItem::upsert(
                        $rows,
                        ['preinventario_id', 'itemable_type', 'itemable_id'],
                        ['cantidad_default', 'updated_at']
                    );
                }

                $allowed = collect($request['items'])
                    ->map(fn($it) => $it['type'] . ':' . (int) $it['id'])
                    ->values()
                    ->all();

                $query = PreinventarioItem::where('preinventario_id', $preinventarioId);

                if (count($allowed) > 0) {
                    $query->whereNotIn(DB::raw("CONCAT(itemable_type, ':', itemable_id)"), $allowed);
                }

                $query->delete();

                return [
                    'status' => true,
                    'message' => 'Detalle del preinventario guardado correctamente.',
                ];
            });
        } catch (ModelNotFoundException $e) {
            return [
                'status' => false,
                'code' => 404,
                'message' => 'Preinventario no encontrado.',
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al guardar el detalle del preinventario.',
                'error' => $e->getMessage(),
            ];
        }
    }
}
