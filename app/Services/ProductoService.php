<?php

namespace App\Services;

use App\Models\Producto;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductoService
{

    public function validarProducto($request, $id = null)
    {
        $rules = [
            'nombre' => ['required', 'string', Rule::unique('productos', 'nombre')->ignore($id)],
            'tipo' => ['required', Rule::in(['COMIDA', 'BEBIDA'])],
            'precio' => ['required', 'numeric', 'min:0'],
            'cocina_id' => ['nullable', 'exists:cocinas,id'],
            'insumo_presentacion_id' => ['nullable', 'exists:insumo_presentaciones,id'],
        ];

        if (!$id) $rules['imagen'] = ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'];

        $messages = [
            'nombre.required' => 'El campo nombre es obligatorio.',
            'nombre.unique' => 'Ya existe un producto con ese nombre.',
            'tipo.required' => 'El campo tipo es obligatorio.',
            'tipo.in' => 'El tipo debe ser COMIDA o BEBIDA.',
            'precio.required' => 'El campo precio es obligatorio.',
            'precio.min' => 'El precio debe ser mayor o igual a 0.',
            'cocina_id.required' => 'El campo cocina es obligatorio.',
            'cocina_id.exists' => 'La cocina no existe.',
            'insumo_presentacion_id.required' => 'El campo insumo es obligatorio.',
            'insumo_presentacion_id.exists' => 'El insumo no existe.',
            'imagen.required' => 'La imagen es obligatoria al crear el producto.',
            'imagen.image' => 'El archivo debe ser una imagen.',
            'imagen.mimes' => 'La imagen debe ser jpeg, png, jpg o gif.',
            'imagen.max' => 'La imagen no debe superar 2MB.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        $validator->sometimes('cocina_id', 'required', function ($input) {
            return ($input->tipo ?? null) === 'COMIDA';
        });

        $validator->sometimes('insumo_presentacion_id', 'required', function ($input) {
            return ($input->tipo ?? null) === 'COMIDA';
        });

        if ($validator->fails()) {
            return [
                'status' => false,
                'errors' => $validator->errors()->all()
            ];
        }

        return [
            'status' => true,
            'message' => 'Validación exitosa'
        ];
    }

    public function crearProducto($request)
    {
        $validacion = $this->validarProducto($request);
        if (!$validacion['status']) return $validacion;

        try {
            $imagen = $request->file('imagen');
            $nameImage = Str::slug($request->nombre) . '_' . time() . '.' . $imagen->getClientOriginalExtension();
            $path = $imagen->storeAs('public/productos', $nameImage);
            $url = Storage::url($path);

            $producto = Producto::create([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'tipo' => $request->tipo,
                'categoria' => $request->categoria,
                'precio' => $request->precio,
                'imagen' => $url,
                'cocina_id' => $request->cocina_id ?? null,
                'insumo_presentacion_id' => $request->insumo_presentacion_id ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            return [
                'status' => true,
                'message' => 'Producto creado correctamente',
                'data' => $producto
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al crear el producto',
                'error' => $e->getMessage()
            ];
        }
    }

    public function obtenerProductos()
    {
        return Producto::with('cocina', 'insumoPresentacion.insumo')->get();
    }

    public function obtenerProductosDisponibles()
    {
        $productos = Producto::with(['cocina', 'insumoPresentacion.insumo'])->where('activo', 1)->get();

        $productos = $productos->filter(function (Producto $p) {
            if ($p->tipo === 'BEBIDA') return true;

            $disp = $this->calcularDisponibilidad($p);
            return $disp !== null && $disp > 0;
        })->values()->map(function (Producto $p) {

            $disponibilidad = $this->calcularDisponibilidad($p);

            return [
                'id' => $p->id,
                'nombre' => $p->nombre,
                'descripcion' => $p->descripcion,
                'imagen' => $p->imagen,
                'tipo' => $p->tipo,
                'categoria' => $p->categoria,
                'precio' => (float) $p->precio,
                'activo' => (bool) $p->activo,
                'cocina' => $p->cocina ? [
                    'id' => $p->cocina->id,
                    'nombre' => $p->cocina->nombre,
                ] : null,
                'insumo_presentacion' => $p->insumoPresentacion ? [
                    'id' => $p->insumoPresentacion->id,
                    'nombre' => $p->insumoPresentacion->nombre,
                    'cantidad_base' => (float) $p->insumoPresentacion->cantidad_base,
                    'stock_insumo_presentacion' => (float) $p->insumoPresentacion->stock,
                    'insumo' => $p->insumoPresentacion->insumo ? [
                        'id' => $p->insumoPresentacion->insumo->id,
                        'nombre' => $p->insumoPresentacion->insumo->nombre,
                        'unidad' => $p->insumoPresentacion->insumo->unidad,
                    ] : null,
                ] : null,
                'cantidad_por_plato' => (float) ($p->cantidad_por_plato ?? 1),
                'disponibilidad' => $p->tipo === 'BEBIDA' ? null : $disponibilidad,
            ];
        });

        return $productos;
    }

    public function calcularDisponibilidad(Producto $p): ?int
    {
        if ($p->tipo === 'COMIDA') {
            $stock = (float) optional($p->insumoPresentacion)->stock ?? 0;
            $cant = (float) ($p->cantidad_por_plato ?? 1);
            if ($cant <= 0) return 0;
            return (int) floor($stock / $cant);
        }
        return null;
    }

    public function actualizarProducto($request, $id)
    {
        $validate = $this->validarProducto($request, $id);
        if (!$validate['status']) return $validate;

        try {
            $producto = Producto::findOrFail($id);
            if ($request->hasFile('imagen')) {

                if ($producto->imagen && file_exists(public_path($producto->imagen))) {
                    unlink(public_path($producto->imagen));
                }

                $imagen = $request->file('imagen');
                $nameImage = Str::slug($request->nombre) . '_' . time() . '.' . $imagen->getClientOriginalExtension();
                $path = $imagen->storeAs('public/productos', $nameImage);
                $producto->imagen = Storage::url($path);
            }
            $producto->update($request->except('imagen'));
            return [
                'status' => true,
                'message' => 'Producto actualizado correctamente',
                'data' => $producto
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al actualizar el producto',
                'error' => $e->getMessage()
            ];
        }
    }

    public function eliminarProducto($id)
    {
        try {
            $producto = Producto::findOrFail($id);
            if ($producto->imagen && file_exists(public_path($producto->imagen))) {
                unlink(public_path($producto->imagen));
            }
            $producto->delete();
            return [
                'status' => true,
                'message' => 'Producto eliminado correctamente'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al eliminar el producto',
                'error' => $e->getMessage()
            ];
        }
    }
}
