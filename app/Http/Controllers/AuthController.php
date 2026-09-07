<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\CambiarRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RecuperarRequest;
use App\Http\Requests\Auth\VerificarRequest;
use App\Http\Resources\AuthUserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{

   public function __construct(protected AuthService $authService) {}

   public function login(LoginRequest $request): JsonResponse
   {
      try {
         $res = $this->authService->login($request->validated());
         if (!$res['status']) return response()->json($res);
         return response()->json([
            'status' => true,
            'message' => 'Sesión iniciada exitosamente',
            'data' => [
               'token' => $res['token'],
               'user' => new AuthUserResource($res['user'], $res['credenciales']),
               'socio' => $res['socio']
            ]
         ]);
      } catch (\Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }

   public function recuperarCuenta(RecuperarRequest $request): JsonResponse
   {
      try {
         $res = $this->authService->recuperarCuenta($request->validated());
         return response()->json($res);
      } catch (\Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }

   public function validarCodigo(VerificarRequest $request): JsonResponse
   {
      try {
         $res = $this->authService->validarCodigo($request->validated());
         return response()->json($res);
      } catch (\Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }

   public function cambiarPassword(CambiarRequest $request): JsonResponse
   {
      try {
         $res = $this->authService->cambiarPassword($request->validated());
         return response()->json($res);
      } catch (\Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }
}
