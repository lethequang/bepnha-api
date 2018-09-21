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
		Route::get('/home/documents', 'bepnha\HomeController@getHomeDocuments');
        Route::get('/home/{tag_id}', 'bepnha\HomeController@getVideos');
		Route::get('search', 'bepnha\HomeController@Search');

		Route::get('/video', 'bepnha\VideoController@getVideos');
        Route::get('/video/search', 'bepnha\VideoController@Search');
        Route::get('/video/getid/{vid}', 'bepnha\VideoController@getVideo');
        Route::get('/video/vinc/{id}', 'bepnha\VideoController@incViewCount');

		Route::get('document', 'bepnha\DocumentController@getDocuments');
		Route::get('document/search', 'bepnha\DocumentController@Search');
		Route::get('/document/vinc/{id}', 'bepnha\DocumentController@incViewCount');

		Route::get('/bachkhoa/maincats', 'bepnha\BachKhoaController@maincats');
		Route::get('/bachkhoa/maincatdocument', 'bepnha\BachKhoaController@maincatDocuments');
        Route::get('/bachkhoa/listcats/{main_cat}', 'bepnha\BachKhoaController@listcats');
        Route::get('/bachkhoa/videos/{main}/{subcat}', 'bepnha\BachKhoaController@getVideos');
        Route::get('/bachkhoa/search/{main}/{subcat}', 'bepnha\BachKhoaController@search');

		Route::get('/bachkhoa/listcatsdocuments/{main_cat}', 'bepnha\BachKhoaController@listcatsdocuments');
		Route::get('/bachkhoa/documents/{main}/{subcat}', 'bepnha\BachKhoaController@getDocuments');
		Route::get('/bachkhoa/documents/search/{main}/{subcat}', 'bepnha\BachKhoaController@searchDocuments');

		Route::get('/note/add/{user_id}/{video_id}', 'bepnha\NotebookController@addNote');
        Route::get('/note/remove/{user_id}/{video_id}', 'bepnha\NotebookController@rmNote');
        Route::get('/note/check/{user_id}/{video_id}', 'bepnha\NotebookController@rmNote');
        Route::get('/note/kinds/{user_id}', 'bepnha\NotebookController@getkinds');
        Route::get('/note/videos/{user_id}', 'bepnha\NotebookController@getVideos');

        Route::get('/note/videosdocuments/{user_id}', 'bepnha\NotebookController@getVideosDocuments');

		Route::get('/notedocument/add/{user_id}/{document_id}', 'bepnha\NotebookDocumentController@addNote');
		Route::get('/notedocument/remove/{user_id}/{document_id}', 'bepnha\NotebookDocumentController@rmNote');
		Route::get('/notedocument/documents/{user_id}', 'bepnha\NotebookDocumentController@getDocuments');
		Route::get('/notedocument/check/{user_id}/{document_id}', 'bepnha\NotebookDocumentController@rmNote');

    });
});