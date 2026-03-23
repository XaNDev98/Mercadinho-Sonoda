<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\MercadinhoController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\RetiradaController;
use App\Http\Controllers\SecullumController;
use App\Http\Controllers\KioskController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('secullum.auth')->group(function () {

    Route::resource('funcionarios', FuncionarioController::class);
    Route::post('/funcionarios/sincronizar', [FuncionarioController::class, 'sincronizar'])
        ->name('funcionarios.sincronizar');
    Route::get('/funcionarios/buscar/{numero_folha}', [FuncionarioController::class, 'buscarPorNumeroFolha'])
        ->name('funcionarios.buscar.numero_folha');

    Route::resource('produtos', ProdutoController::class);
    Route::post('/produtos/importar', [ProdutoController::class, 'importar'])
        ->name('produtos.importar');

    Route::resource('retiradas', RetiradaController::class)->only([
        'index', 'create', 'store'
    ]);
    Route::get('/retiradas/exportar', [RetiradaController::class, 'exportar'])
        ->name('retiradas.exportar');

    Route::post('/categorias', [CategoriaController::class, 'store'])
        ->name('categorias.store');

    Route::post('/buscar-funcionario', [MercadinhoController::class, 'buscarFuncionario'])
        ->name('buscar.funcionario');

    Route::get('/teste-token', [SecullumController::class, 'token']);
    Route::get('/teste-bancos', [SecullumController::class, 'bancos']);
    Route::get('/funcionario/{folha}', [SecullumController::class, 'buscarFuncionario']);
    Route::get('/teste-secullum', [SecullumController::class, 'testarEndpoints']);
    Route::get('/teste-funcionarios', [SecullumController::class, 'funcionarios']);

    Route::get('/kiosk', [KioskController::class, 'index'])->name('kiosk');
    Route::post('/kiosk/unlock', [KioskController::class, 'unlock'])->name('kiosk.unlock');
    Route::post('/kiosk/lock', [KioskController::class, 'lock'])->name('kiosk.lock');

    Route::post('/kiosk/ativar', [KioskController::class, 'ativarModoFixo'])->name('kiosk.ativar');
    Route::post('/kiosk/desativar', [KioskController::class, 'desativarModoFixo'])->name('kiosk.desativar'); 

    
Route::get('/debug-imagem', function () {
    $arquivo = 'produtos/Za3XOvFRgDabvWfzLXQv2faf1PVtLa3kmLTfTgJY.jpg';

    return response()->json([
        'storage_link_existe' => file_exists(public_path('storage')),
        'storage_link_is_link' => is_link(public_path('storage')),
        'arquivo_em_storage_app_public' => file_exists(storage_path('app/public/' . $arquivo)),
        'arquivo_em_public_storage' => file_exists(public_path('storage/' . $arquivo)),
        'public_storage_path' => public_path('storage/' . $arquivo),
        'storage_app_public_path' => storage_path('app/public/' . $arquivo),
    ]);
});
