<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
/*
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
*/

Route::get('/', function () {
    return response()->json([
        'Backend Multiplier' => [
                [
                    '[ POST ]  /api/login' => [
                        '{email}' => 'email cadastrado para identificar usuário',
                        '{password}' => 'senha do sistema.'
                    ],

                    '[ POST ]  /api/register  (usuário no grupo clientes)' => [
                        "{name}" => "Nome do usuário",
                        "{cpf}" => "Número de Cadastro de Pessoa física do Usuário",
                        "email" => "Email do usuário utilizado para login no sistema",
                        "password" => "Senha de Usuário"
                    ],
                ],
                ['[ POST ]  /api/logout' => 'Encerra seção'],
                ['[ POST ]  /api/me'     => 'Quem está logado?'],
                ['/api'                  => 'recursos disponíveis do sistema.'],
                ['/api/register'         => 'Cadastro de clientes'],
                ['/api/user'             => 'Ususários do sistema'],
                ['/api/order'            => 'Pedidos -> Vendas'],
                ['/api/orderItems'       => 'PedidoItems -> VendaItems'],
                ['/api/orderReport' => 'relatórios'],

        ]
    ]);
});


Route::get('login/help', [\App\Http\Controllers\Auth\Api\LoginController::class, 'loginHelp']);
Route::get('login',      [\App\Http\Controllers\Auth\Api\LoginController::class, 'loginHelp']);
Route::post('login',     [\App\Http\Controllers\Auth\Api\LoginController::class, 'login']);
Route::post('logout',    [\App\Http\Controllers\Auth\Api\LoginController::class, 'logout']);
Route::post('me',        [\App\Http\Controllers\Auth\Api\LoginController::class, 'me']);

Route::get('register',       [\App\Http\Controllers\Auth\Api\LoginController::class, 'registerHelp']);
Route::post('register',      [\App\Http\Controllers\Auth\Api\LoginController::class, 'register']);
Route::get('register/help',  [\App\Http\Controllers\Auth\Api\LoginController::class, 'registerHelp']);
Route::post('register/help', [\App\Http\Controllers\Auth\Api\LoginController::class, 'registerHelp']);


Route::group(['prefix' => 'orderReport','as' => 'api','middleware' => ['auth:sanctum']], function () {
    $Controller = '\App\Http\Controllers\OrderReportController';
    Route::get("/{id}/{iid}/{iiid}",  [$Controller,  'show']);
    Route::post("/{id}/{iid}/{iiid}", [$Controller,  'show']);
    Route::get("/{id}/{iid}",  [$Controller,  'show']);
    Route::post("/{id}/{iid}", [$Controller,  'show']);
    Route::get("/{id}",        [$Controller,  'show']);
    Route::post("/{id}",       [$Controller,  'show']);
    Route::get('/',            [$Controller, 'index']);
});


Route::group(['prefix' => 'board','as' => 'api','middleware' => ['auth:sanctum']], function () {
    $Controller = '\App\Http\Controllers\BoardController';
    Route::get("/{id}",  [$Controller,   'show']);
    Route::get("/",      [$Controller,  'index']);
    Route::post("/{id}", [$Controller,   'show']);
    Route::post("/",     [$Controller,  'store']);
});

Route::group(['prefix' => 'user','as' => 'api','middleware' => ['auth:sanctum']], function () {
    $Controller = '\App\Http\Controllers\UserController';
    Route::get("/{id}",  [$Controller,    'show']);
    Route::get('/',      [$Controller,   'index']);
    Route::post("/{id}", [$Controller,    'show']);
    Route::post('/',     [$Controller,   'store']);
    Route::put("/{id}",  [$Controller,    'show']);
    Route::put('/',      [$Controller,  'update']);
    Route::delete('/',   [$Controller, 'destroy']);
});

Route::group(['prefix' => 'menu','as' => 'api','middleware' => ['auth:sanctum']], function () {
    $Controller = '\App\Http\Controllers\MenuController';
    Route::get("/{id}",  [$Controller,    'show']);
    Route::get('/',      [$Controller,   'index']);
    Route::post("/{id}", [$Controller,    'show']);
    Route::post('/',     [$Controller,   'store']);
    Route::delete('/',   [$Controller, 'destroy']);
});

Route::group(['prefix' => 'order','as' => 'api','middleware' => ['auth:sanctum']], function () {
    $Controller = '\App\Http\Controllers\OrderController';
    Route::get('/',        [$Controller,   'index']);
    Route::get('/{id}',    [$Controller,    'show']);
    Route::post("/{id}",   [$Controller,    'show']);
    Route::post('/',       [$Controller,   'store']);
    Route::put("/{id}",    [$Controller,  'update']);
    Route::put("/",        [$Controller,  'update']);
    Route::delete("/{id}", [$Controller,  'update']);
    Route::delete('/',     [$Controller, 'destroy']);
});

Route::group(['prefix' => 'orderItems','as' => 'api','middleware' => ['auth:sanctum']], function () {
    $Controller = '\App\Http\Controllers\OrderItemController';
    Route::get('/',        [$Controller,   'index']);
    Route::get('/{id}',    [$Controller,    'show']);
    Route::post('/{id}',   [$Controller,    'show']);
    Route::post('/',       [$Controller,   'store']);
    Route::put("/{id}",    [$Controller,  'update']);
    Route::put("/",        [$Controller,  'update']);
    Route::delete("/{id}", [$Controller,  'update']);
    Route::delete('/',     [$Controller, 'destroy']);
});

Route::prefix('auth')->group(function() {
    $controller = '\App\Http\Controllers\Auth\Api\LoginController';
    Route::post('logout', [$controller, 'logout']);
    Route::get('me', [$controller, 'me']);
});
