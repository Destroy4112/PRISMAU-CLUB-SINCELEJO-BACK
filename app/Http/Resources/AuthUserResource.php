<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthUserResource extends JsonResource
{
    protected ?array $credenciales;

    public function __construct(mixed $resource, ?array $credenciales = null)
    {
        parent::__construct($resource);
        $this->credenciales = $credenciales;
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'imagen' => $this->imagen,
            'user_id' => $this->user_id,
            'nombre' => $this->Nombre,
            'apellidos' => $this->Apellidos,
            'tipoDocumento' => $this->TipoDocumento ?? null,
            'documento' => $this->credenciales['Documento'] ?? null,
            'telefono' => $this->Telefono ?? null,
            'correo' => $this->Correo ?? null,
            'direccion' => $this->DireccionResidencia ?? null,
            'estado' => $this->Estado,
            'parentesco' => $this->Parentesco ? "pareja" : null,
            'rol' => $this->credenciales['Rol'] ?? null,
        ];
    }
}
