<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\FileManagerController;
use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\LogManagerController;
use App\Http\Controllers\Site\WordPressController;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    if (filter_var(request()->getHttpHost(), FILTER_VALIDATE_IP) || request()->getHttpHost() == \App\Models\Site::where(['panel' => 1])->pluck('domain')->first()) {
        return view('welcome');
    }
    return 'Domain/Subdomain not configured on this Server!';
});


// Route::get('/login', function () {
//     return view('login');
// })->name('login');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified' ])->group(function () {

    Route::get('/design', function () {
        return view('design');
    })->name('design');

    //phpmyadmin route
    Route::get('/pma', function () {
        return redirect()->to('mysecureadmin/index.php');
    });

    //phpmyadmin route with autologin
    Route::get('/autopma/{site_id}', [NodejsController::class, 'autoLoginPMA'])->name('autopma');
  


    Route::get('/pdf/{site_id}/{token}', [SiteController::class, 'pdf']);

    Route::get('files/{folder_name?}', [FileManagerController::class,'index'])->where('folder_name', '(.*)')->name('files.index');
    Route::post('files/view', [FileManagerController::class, 'show'])->name('files.show');
    Route::post('files/edit', [FileManagerController::class, 'edit'])->name('files.edit');
    Route::post('files/store', [FileManagerController::class, 'store'])->name('files.store');
    Route::post('files/download', [FileManagerController::class, 'download'])->name('files.download');
    Route::post('files/create-directory', [FileManagerController::class, 'createDirectory'])->name('files.create.directory');
    Route::post('files/create-file', [FileManagerController::class, 'createFile'])->name('files.create.file');
    Route::post('files/rename-file', [FileManagerController::class, 'renameFile'])->name('files.rename.file');
    Route::post('files/copy-file', [FileManagerController::class, 'copy'])->name('files.copy');
    Route::post('files/move-file', [FileManagerController::class, 'move'])->name('files.move');
    Route::post('files/delete', [FileManagerController::class, 'destroy'])->name('files.delete');

    Route::get('download_file_object/{id}', [FileManagerController::class, 'downloadObject']);
    Route::get('show-media-file/{id}', [FileManagerController::class, 'showMediaFile']);
});
