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

define ('mainRoute',  [
    'Backend Multiplier' =>
    [
        '/api' => 'recursos disponíveis do sistema.',
            '/api/login' => 'logar no sistema (não permitido para CLIENTES)',
            '/api/register' => '(registrar usuário no grupo clientes)',
            '[ POST ]  /api/logout' => 'Encerra seção',
            '[ POST ]  /api/me'     => 'Quem está logado?'],
        '/api/user'   => 'Ususários do sistema',
        '/api/order'  => 'Pedidos -> Vendas',
        '/api/orderItems'   => 'PedidoItems -> VendaItems',
        '/api/orderReport' => 'relatórios',
    ]);

Route::get('/', function () {
    return response()->json(mainRoute);
});

Route::post('/', function () {
    return response()->json(mainRoute);
});

$controller = '\App\Http\Controllers\Auth\Api\LoginController';
Route::get('login/help',     [$controller, 'loginHelp']);
Route::get('login',          [$controller, 'loginHelp']);
Route::post('login',         [$controller, 'login']);
Route::get('logout',         [$controller, 'logout']);
Route::post('logout',        [$controller, 'logout']);
Route::post('me',            [$controller, 'me']);
Route::get('register',       [$controller, 'registerHelp']);
Route::post('register',      [$controller, 'register']);
Route::get('register/help',  [$controller, 'registerHelp']);
Route::post('register/help', [$controller, 'registerHelp']);


Route::group(['prefix' => 'orderReport','as' => 'api','middleware' => ['auth:sanctum']], function () {
    $controller = '\App\Http\Controllers\OrderReportController';
    Route::get("/{id}/{iid}",  [$controller,  'show']);
    Route::post("/{id}/{iid}", [$controller,  'show']);
    Route::get("/{id}",        [$controller,  'show']);
    Route::post("/{id}",       [$controller,  'show']);
    Route::get('/',            [$controller, 'index']);
});


Route::group(['prefix' => 'board','as' => 'api','middleware' => ['auth:sanctum']], function () {
    $controller = '\App\Http\Controllers\BoardController';
    Route::get("/{id}",  [$controller,   'show']);
    Route::get("/",      [$controller,  'index']);
    Route::post("/{id}", [$controller,   'show']);
    Route::post("/",     [$controller,  'store']);
});

Route::group(['prefix' => 'user','as' => 'api','middleware' => ['auth:sanctum']], function () {
    $controller = '\App\Http\Controllers\UserController';
    Route::get("/{id}",  [$controller,    'show']);
    Route::get('/',      [$controller,   'index']);
    Route::post("/{id}", [$controller,    'show']);
    Route::post('/',     [$controller,   'store']);
    Route::put("/{id}",  [$controller,    'show']);
    Route::put('/',      [$controller,  'update']);
    Route::delete('/',   [$controller, 'destroy']);
});

Route::group(['prefix' => 'menu','as' => 'api','middleware' => ['auth:sanctum']], function () {
    $controller = '\App\Http\Controllers\MenuController';
    Route::get("/{id}",  [$controller,    'show']);
    Route::get('/',      [$controller,   'index']);
    Route::post("/{id}", [$controller,    'show']);
    Route::post('/',     [$controller,   'store']);
    Route::delete('/',   [$controller, 'destroy']);
});

Route::group(['prefix' => 'order','as' => 'api','middleware' => ['auth:sanctum']], function () {
    $controller = '\App\Http\Controllers\OrderController';
    Route::get('/',        [$controller,   'index']);
    Route::get('/{id}',    [$controller,    'show']);
    Route::post("/{id}",   [$controller,    'show']);
    Route::post('/',       [$controller,   'store']);
    Route::put("/{id}",    [$controller,  'update']);
    Route::put("/",        [$controller,  'update']);
    Route::delete("/{id}", [$controller,  'update']);
    Route::delete('/',     [$controller, 'destroy']);
});

Route::group(['prefix' => 'orderItems','as' => 'api','middleware' => ['auth:sanctum']], function () {
    $controller = '\App\Http\Controllers\OrderItemController';
    Route::get('/',        [$controller,   'index']);
    Route::get('/{id}',    [$controller,    'show']);
    Route::post('/{id}',   [$controller,    'show']);
    Route::post('/',       [$controller,   'store']);
    Route::put("/{id}",    [$controller,  'update']);
    Route::put("/",        [$controller,  'update']);
    Route::delete("/{id}", [$controller,  'update']);
    Route::delete('/',     [$controller, 'destroy']);
});

Route::prefix('auth')->group(function() {
    $controller = '\App\Http\Controllers\Auth\Api\LoginController';
    Route::post('logout', [$controller, 'logout']);
    Route::get('me', [$controller, 'me']);
});
