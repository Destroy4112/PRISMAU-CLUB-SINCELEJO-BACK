<?php

namespace App\Services;

use App\Mail\CodigoMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
   private const RESET_CODE_EXPIRATION_MINUTES = 15;

   public function __construct(protected UserService $userService) {}

   public function login(array $request): array
   {
      $token = JWTAuth::attempt([
         'Documento' => $request['Documento'],
         'password' => $request['password'],
      ]);

      if (!$token) {
         return [
            'status' => false,
            'errors' => ['Credenciales inválidas'],
         ];
      }

      $user = JWTAuth::user();
      $usuario = null;
      $socio = null;

      if ((int) $user->Rol === 1 && optional($user->admin)->Estado === 0) {
         return [
            'status' => false,
            'errors' => ['Usuario inactivo'],
         ];
      }

      $usuario = match ((int) $user->Rol) {
         0, 1 => $user->admin,
         2 => $user->asociado,
         3 => $user->adherente,
         4, 6, 7 => $user->empleado,
         5 => $user->familiar,
         default => null
      };

      if ((int) $user->Rol === 5 && optional($user->familiar)->Parentesco === 'Esposo (a)') {
         $familiar = $user->familiar;

         if ($familiar?->asociado) {
            $socio = $familiar->asociado;
            $socio->Rol = 2;
         } elseif ($familiar?->adherente) {
            $socio = $familiar->adherente;
            $socio->Rol = 3;
         }
      }

      return [
         'status' => true,
         'token' => $token,
         'user' => $usuario,
         'credenciales' => $user->only(['id', 'Documento', 'Rol']),
         'socio' => $socio,
      ];
   }

   public function recuperarCuenta(array $request): array
   {
      $user = $this->userService->getUserByDocumento($request['Documento']);

      if (!$user) {
         return [
            'status' => false,
            'errors' => ['Usuario no encontrado'],
         ];
      }

      $usuario = match ((int) $user->Rol) {
         2 => $user->asociado,
         3 => $user->adherente,
         4, 6, 7 => $user->empleado,
         5 => $user->familiar,
         default => null,
      };

      $plainToken = Str::upper(Str::random(6));
      $hashedToken = Hash::make($plainToken);

      DB::table('password_reset_tokens')->updateOrInsert(
         ['email' => $request['Documento']],
         [
            'token' => $hashedToken,
            'created_at' => now(),
         ]
      );

      Mail::to($usuario->Correo)->send(new CodigoMail($plainToken));

      return [
         'status' => true,
         'message' => 'Código de restablecimiento enviado. Vigencia: 15 minutos.',
      ];
   }

   public function validarCodigo(array $request): array
   {
      $row = $this->getPasswordResetRowByDocumento($request['Documento']);

      if (!$row) {
         return [
            'status' => false,
            'errors' => ['Código inválido o no encontrado'],
         ];
      }

      if ($this->isResetTokenExpired($row->created_at)) {
         return [
            'status' => false,
            'errors' => ['El código ha vencido'],
         ];
      }

      if (!Hash::check($request['code'], $row->token)) {
         return [
            'status' => false,
            'errors' => ['Código inválido'],
         ];
      }

      return [
         'status' => true,
         'message' => 'Código validado exitosamente',
      ];
   }

   public function cambiarPassword(array $request): array
   {
      $row = $this->getPasswordResetRowByDocumento($request['Documento']);

      if (!$row) {
         return [
            'status' => false,
            'errors' => ['Código inválido o no encontrado'],
         ];
      }

      if ($this->isResetTokenExpired($row->created_at)) {
         return [
            'status' => false,
            'errors' => ['El código ha vencido'],
         ];
      }

      if (!Hash::check($request['code'], $row->token)) {
         return [
            'status' => false,
            'errors' => ['Código inválido'],
         ];
      }

      $user = $this->userService->getUserByDocumento($request['Documento']);

      if (!$user) {
         return [
            'status' => false,
            'errors' => ['Usuario no encontrado'],
         ];
      }

      $user->password = Hash::make($request['new_password']);
      $user->save();

      DB::table('password_reset_tokens')
         ->where('email', $request['Documento'])
         ->delete();

      return [
         'status' => true,
         'message' => 'Contraseña cambiada exitosamente',
      ];
   }

   private function getPasswordResetRowByDocumento(string $documento): ?object
   {
      return DB::table('password_reset_tokens')
         ->where('email', $documento)
         ->first();
   }

   private function isResetTokenExpired(string $createdAt): bool
   {
      return Carbon::parse($createdAt)
         ->addMinutes(self::RESET_CODE_EXPIRATION_MINUTES)
         ->isPast();
   }
}
