<?php

use App\Http\Controllers\AdherenteController;
use App\Http\Controllers\AdminsController;
use App\Http\Controllers\AsociadoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContratosController;
use App\Http\Controllers\CuotasBaileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DisponibilidadEspacioController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\EncuestasController;
use App\Http\Controllers\EntradaController;
use App\Http\Controllers\EspacioController;
use App\Http\Controllers\EstadosController;
use App\Http\Controllers\ExpoTokenController;
use App\Http\Controllers\FacturacionController;
use App\Http\Controllers\FamiliarController;
use App\Http\Controllers\InvitadoController;
use App\Http\Controllers\MensualidadesController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MenuRoleController;
use App\Http\Controllers\NoticiaController;
use App\Http\Controllers\PreguntasController;
use App\Http\Controllers\ReservasController;
use App\Http\Controllers\RespuestasController;
use App\Http\Controllers\RubrosController;
use App\Http\Controllers\SolicitudesController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\WebhookFacturacionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
   return $request->user();
});

//auth
Route::post("login", [AuthController::class, "login"]); //web y mobile
Route::post('reset-password', [AuthController::class, 'recuperarCuenta']); //web y mobile
Route::post('verify-reset-code', [AuthController::class, 'validarCodigo']); //web y mobile
Route::post('change-password', [AuthController::class, 'cambiarPassword']); //web y mobile

Route::post("register", [ContratosController::class, "create"]); //mobile

Route::post('webhook', [WebhookFacturacionController::class, 'handleWebhook']); //web y mobile

Route::group([
   "middleware" => ["auth:api"]
], function () {

   //Dashboard
   Route::get('dashboard/stats', [DashboardController::class, 'stats']); //web

   //admin 
   Route::get('admin', [AdminsController::class, 'getAll']); //web
   Route::post('admin', [AdminsController::class, 'create']); //web
   Route::put('admin/{id}', [AdminsController::class, 'update']); //web
   Route::put('admin/status/{id}', [AdminsController::class, 'changeStatus']); //web
   Route::delete('admin/{id}', [AdminsController::class, 'delete']); //web

   //Menu
   Route::post('menus', [MenuController::class, 'create']); //web
   Route::get('menus', [MenuController::class, 'getAll']); //web
   Route::put('menus/{id}', [MenuController::class, 'update']); //web
   Route::delete('menus/{id}', [MenuController::class, 'delete']); //web

   Route::post('menus/rol', [MenuRoleController::class, 'assign']); //web
   Route::get('menus/rol/{id}', [MenuRoleController::class, 'getByRole']); //web
   Route::get('menus/rol/{id}/tipos', [MenuRoleController::class, 'getByMenuType']); //mobile
   Route::delete('menus/rol/{id}', [MenuRoleController::class, 'delete']); //web

   //contratos app
   Route::get('contratos', [ContratosController::class, 'get']); //web

   //solicitudes
   Route::post('solicitudes', [SolicitudesController::class, 'create']); //mobile
   Route::get('solicitudes', [SolicitudesController::class, 'getAll']); //web
   Route::get('solicitudes/pendientes', [SolicitudesController::class, 'contPendientes']); //mobile
   Route::get('solicitudes/cantidad/{id}', [SolicitudesController::class, 'contByUser']); //mobile
   Route::get('solicitudes/{id}', [SolicitudesController::class, 'get']); //mobile
   Route::get('solicitudes/user/{id}', [SolicitudesController::class, 'getByUser']); //mobile
   Route::put('solicitudes/{id}', [SolicitudesController::class, 'reply']); //web

   //Reservas
   Route::post('reservas', [ReservasController::class, 'create']); //mobile
   Route::get('reservas', [ReservasController::class, 'getAll']); //web
   Route::get('reservas/cantidad', [ReservasController::class, 'cont']); //mobile
   Route::get('reservas/cantidad/{id}', [ReservasController::class, 'contByUser']); //mobile
   Route::get('reservas/{id}', [ReservasController::class, 'getByUser']); //mobile
   Route::delete('reservas/{id}', [ReservasController::class, 'cancel']); //mobile

   //Encuestas
   Route::post('encuestas', [EncuestasController::class, 'create']); //web
   Route::post('encuestas/{id}/responder', [EncuestasController::class, 'saveReplies']); //mobile
   Route::get('encuestas', [EncuestasController::class, 'getAll']); //web
   Route::get('encuestas/disponibles/{id}', [EncuestasController::class, 'getAvailable']); //mobile
   Route::get('encuestas/respuestas/{id}', [EncuestasController::class, 'usersReply']); //web
   Route::get('encuestas/{id}', [EncuestasController::class, 'get']); //mobile
   Route::put('encuestas/{id}', [EncuestasController::class, 'update']); //web
   Route::delete('encuestas/{id}', [EncuestasController::class, 'delete']); //web

   //Preguntas
   Route::post('preguntas', [PreguntasController::class, 'create']); //web
   Route::get('preguntas/encuesta/{id}', [PreguntasController::class, 'getByEncuesta']); //web
   Route::put('preguntas/{id}', [PreguntasController::class, 'update']); //web
   Route::delete('preguntas/{id}', [PreguntasController::class, 'delete']); //web

   //Respuestas
   Route::post('respuestas', [RespuestasController::class, 'create']); //web
   Route::get('respuestas/{id}', [RespuestasController::class, 'getByPregunta']); //web
   Route::put('respuestas/{id}', [RespuestasController::class, 'update']); //web
   Route::delete('respuestas/{id}', [RespuestasController::class, 'delete']); //web

   //usuario
   Route::get('socios/pagos', [UsuarioController::class, 'getSociosForPayments']); //web
   Route::get('usuario/saldos', [UsuarioController::class, 'getSaldosSocios']); //mobile
   Route::get('usuario/contabilidad', [UsuarioController::class, 'getContabilidadGeneral']); //web y mobile
   Route::get('usuario/{documento}', [UsuarioController::class, 'getByDocumento']); //web y mobile
   Route::put('usuario/{id}', [UsuarioController::class, 'changePassword']); //web y mobile
   Route::put('usuario/reset-password/{id}', [UsuarioController::class, 'resetPassword']); //web
   Route::delete('usuario/{id}', [UsuarioController::class, 'eliminarCuenta']);

   // asociados
   Route::post('asociados', [AsociadoController::class, 'create']); //web
   Route::post('asociados/imagen/{id}', [AsociadoController::class, 'changeImagen']); //web
   Route::get('asociados', [AsociadoController::class, 'getAll']); //web
   Route::get('asociados/all', [AsociadoController::class, 'getAsociados']); //web
   Route::get('asociados/familiares/{id}', [AsociadoController::class, 'asociadoWithFamiliars']); //mobile
   Route::put('asociados/{asociado}', [AsociadoController::class, 'update']); //web
   Route::put('asociados/status/{id}', [AsociadoController::class, 'changeStatus']); //web
   Route::delete('asociados/{id}', [AsociadoController::class, 'delete']); //web
   Route::delete('asociados/imagen/{id}', [AsociadoController::class, 'deleteImagen']); //web

   // adherentes
   Route::post('adherentes', [AdherenteController::class, 'create']); //web
   Route::post('adherentes/imagen/{id}', [AdherenteController::class, 'changeImagen']); //web
   Route::get('adherentes', [AdherenteController::class, 'getAll']); //web
   Route::get('adherentes/familiares/{id}', [AdherenteController::class, 'adherenteWithFamiliars']); //mobile
   Route::put('adherentes/{adherente}', [AdherenteController::class, 'update']); //web
   Route::put('adherentes/status/{id}', [AdherenteController::class, 'changeStatus']); //web
   Route::put('adherentes/asociado/{id}', [AdherenteController::class, 'changeToAsociado']); //web
   Route::delete('adherentes/{id}', [AdherenteController::class, 'delete']); //web
   Route::delete('adherentes/imagen/{id}', [AdherenteController::class, 'deleteImagen']); //web

   //empleados
   Route::post('empleados', [EmpleadoController::class, 'create']); //web
   Route::post('empleados/imagen/{id}', [EmpleadoController::class, 'changeImagen']); //web
   Route::get('empleados', [EmpleadoController::class, 'getAll']); //web
   Route::put('empleados/{empleado}', [EmpleadoController::class, 'update']); //web
   Route::delete('empleados/{id}', [EmpleadoController::class, 'delete']); //web
   Route::delete('empleados/imagen/{id}', [EmpleadoController::class, 'deleteImagen']); //web

   //familiares
   Route::post('familiares', [FamiliarController::class, 'create']); //web
   Route::post('familiares/imagen/{id}', [FamiliarController::class, 'changeImagen']); //web
   Route::get('familiares/pareja/{id}', [FamiliarController::class, 'nucleoDesdeEsposa']); //mobile
   Route::get('familiares/cantidad/{id}/{rol}', [FamiliarController::class, 'contSocio']); //mobile
   Route::get('familiares/{id}/{rol}', [FamiliarController::class, 'get']); //web y mobile
   Route::put('familiares/{familiar}', [FamiliarController::class, 'update']); //web
   Route::delete('familiares/{id}', [FamiliarController::class, 'delete']); //web
   Route::delete('familiares/imagen/{id}', [FamiliarController::class, 'deleteImagen']); //web

   //espacios
   Route::post('espacios', [EspacioController::class, 'create']); //web
   Route::get('espacios', [EspacioController::class, 'getPaginated']); //web
   Route::get('espacios/all', [EspacioController::class, 'getAll']); //mobile
   Route::put('espacios/{espacio}', [EspacioController::class, 'update']); //web
   Route::delete('espacios/{id}', [EspacioController::class, 'delete']); //web

   //Disponibilidades de Espacios
   Route::post('disponibilidad-espacio', [DisponibilidadEspacioController::class, 'save']);
   Route::get('disponibilidad-espacio/{id}', [DisponibilidadEspacioController::class, 'get']);

   //noticias
   Route::post('noticias', [NoticiaController::class, 'create']); //web
   Route::get('noticias', [NoticiaController::class, 'getAll']); //web y mobile
   Route::put('noticias/{noticia}', [NoticiaController::class, 'update']); //web
   Route::delete('noticias/{id}', [NoticiaController::class, 'delete']); //web

   //invitados
   Route::post('invitados', [InvitadoController::class, 'create']); //web y mobile
   Route::post('invitados/imagen/{id}', [InvitadoController::class, 'saveImagen']); //mobile
   Route::get('invitados', [InvitadoController::class, 'getAll']); //web
   Route::get('invitados/entradas', [InvitadoController::class, 'getEntradas']); //mobile
   Route::put('invitados/{id}', [InvitadoController::class, 'updateEntrada']); //mobile

   //entradas
   Route::post('entradas/{id}', [EntradaController::class, 'create']);
   Route::get('entradas', [EntradaController::class, 'getAll']);

   //estados
   Route::get('estados', [EstadosController::class, 'getAll']); //web

   //Rubros
   Route::post('rubros', [RubrosController::class, 'create']); //web
   Route::get('rubros/all', [RubrosController::class, 'getAll']); //web
   Route::get('rubros', [RubrosController::class, 'getPaginated']); //web
   Route::put('rubros/{rubro}', [RubrosController::class, 'update']); //web
   Route::delete('rubros/{id}', [RubrosController::class, 'delete']); //web

   //Facturas
   Route::post('facturas', [FacturacionController::class, 'generate']);
   Route::post('facturas/valor', [FacturacionController::class, 'updateBillyingsValue']);

   //Mensualidades
   Route::post('mensualidades', [MensualidadesController::class, 'pay']); //web
   Route::post('mensualidades/preference', [MensualidadesController::class, 'createPreference']); //web y mobile
   Route::get('mensualidades/{documento}', [MensualidadesController::class, 'getByDocumento']); //web y mobile
   Route::get('mensualidades/{paymentId}/resume', [MensualidadesController::class, 'resumePaymentMercadoPago']); //web y mobile

   //Cuotas Baile
   Route::post('cuotas', [CuotasBaileController::class, 'pay']); //web
   Route::post('cuotas/preference', [CuotasBaileController::class, 'createPreference']); //web y mobile
   Route::get('cuotas/{documento}', [CuotasBaileController::class, 'getByDocumento']); //web y mobile
   Route::get('cuotas/{paymentId}/resume', [CuotasBaileController::class, 'resumePaymentMercadoPago']); //web y mobile

   //Push notifications
   Route::post('expo/token', [ExpoTokenController::class, 'store']); //mobile
});
