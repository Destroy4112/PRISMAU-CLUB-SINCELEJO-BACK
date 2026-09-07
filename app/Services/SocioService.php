<?php

namespace App\Services;

use App\Models\Adherente;
use App\Models\Asociado;
use App\Models\Familiar;

class SocioService
{

    public function cambiarEstadoGrupo(int $asociadoId, int $estado): void
    {
        $adherenteIds = Adherente::where('asociado_id', $asociadoId)->pluck('id');
        if (empty($adherenteIds)) {
            return;
        }
        Adherente::whereIn('id', $adherenteIds)->update(['Estado' => $estado]);
        Familiar::whereIn('adherente_id', $adherenteIds)->update(['Estado' => $estado]);
    }

    public function changeAdherenteToAsociado(int $userId): void
    {
        $adherente = Adherente::with('familiares')->where('user_id', $userId)->firstOrFail();
        $asociado = Asociado::create([
            'user_id'             => $adherente->user_id,
            'imagen'              => $adherente->imagen,
            'Nombre'              => $adherente->Nombre,
            'Apellidos'           => $adherente->Apellidos,
            'TipoDocumento'       => $adherente->TipoDocumento,
            'Documento'           => $adherente->Documento,
            'Correo'              => $adherente->Correo,
            'Telefono'            => $adherente->Telefono,
            'FechaNacimiento'     => $adherente->FechaNacimiento,
            'LugarNacimiento'     => $adherente->LugarNacimiento,
            'Sexo'                => $adherente->Sexo,
            'DireccionResidencia' => $adherente->DireccionResidencia,
            'CiudadResidencia'    => $adherente->CiudadResidencia,
            'TiempoResidencia'    => $adherente->TiempoResidencia,
            'EstadoCivil'         => $adherente->EstadoCivil,
            'Profesion'           => $adherente->Profesion,
            'Trabajo'             => $adherente->Trabajo,
            'Cargo'               => $adherente->Cargo,
            'TiempoServicio'      => $adherente->TiempoServicio,
            'TelOficina'          => $adherente->TelOficina,
            'DireccionOficina'    => $adherente->DireccionOficina,
            'CiudadOficina'       => $adherente->CiudadOficina,
            'Estado'              => $adherente->Estado,
            'Codigo'              => $adherente->Codigo ?? null,
        ]);

        foreach ($adherente->familiares as $fam) {
            $fam->update([
                'asociado_id'  => $asociado->id,
                'adherente_id' => null,
            ]);
        }

        $adherente->delete();
    }
}
