<?php

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
    return view('welcome');
});

Route::get('/viewlogfile', function () {
    return file_get_contents('../storage/logs/laravel.log');
});

Route::group(['prefix' => 'api'], function () {
    Route::group(['prefix' => 'ottbox'], function (){
        Route::get('/home', 'bepnha\HomeController@index');
        Route::get('/home/videos', 'bepnha\HomeController@getHomeVideos');
        Route::get('/home/{tag_id}', 'bepnha\HomeController@getVideos');

        Route::get('/video', 'bepnha\VideoController@getVideos');
        Route::get('/video/search', 'bepnha\VideoController@Search');
        Route::get('/video/getid/{vid}', 'bepnha\VideoController@getVideo');
        Route::get('/video/vinc/{id}', 'bepnha\VideoController@incViewCount');
		Route::get('/video/search2', 'bepnha\VideoController@Search2');



		Route::get('/bachkhoa/maincats', 'bepnha\BachKhoaController@maincats');
        Route::get('/bachkhoa/listcats/{main_cat}', 'bepnha\BachKhoaController@listcats');
        Route::get('/bachkhoa/videos/{main}/{subcat}', 'bepnha\BachKhoaController@getVideos');
        Route::get('/bachkhoa/search/{main}/{subcat}', 'bepnha\BachKhoaController@search');

        Route::get('/note/add/{user_id}/{video_id}', 'bepnha\NotebookController@addNote');
        Route::get('/note/remove/{user_id}/{video_id}', 'bepnha\NotebookController@rmNote');
        Route::get('/note/check/{user_id}/{video_id}', 'bepnha\NotebookController@rmNote');
        Route::get('/note/kinds/{user_id}', 'bepnha\NotebookController@getkinds');
        Route::get('/note/videos/{user_id}', 'bepnha\NotebookController@getVideos');

        Route::get('document/search', 'bepnha\DocumentController@Search');
        Route::get('search', 'bepnha\HomeController@Search');
    });
});