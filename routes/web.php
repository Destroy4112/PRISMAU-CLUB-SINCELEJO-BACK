<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/seed-admin', function () {
    Artisan::call('db:seed', [
        '--class' => 'AdminSeeder',
    ]);
    return 'ejecutado.';
});

Route::get('/migrate', function () {
    Artisan::call('migrate');
    return 'Migraciones ejecutadas.';
});

Route::get('/migrate-fresh', function () {
    Artisan::call('migrate:fresh');
    return 'Migraciones ejecutadas.';
});

Route::get('/refresh-cache', function () {
    Artisan::call('config:clear');
    return 'Cache limpia.';
});

Route::get('/storage-link', function () {
    Artisan::call('storage:link');
    return 'Acceso directo creado.';
});

Route::get('/storage-link-create', function () {
    $targetFolder = storage_path('app/public');
    $linkFolder = $_SERVER['DOCUMENT_ROOT'] . '/storage';
    symlink($targetFolder, $linkFolder);
});

Route::get('/test-push/{id}', function ($id) {
    $user = \App\Models\User::find($id);
    $user->notify(new \App\Notifications\Notificacion('Prueba', 'Cuerpo de la notificación'));
    return 'Notificación enviada';
});