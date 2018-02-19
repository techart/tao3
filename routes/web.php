<?php
Route::get('/', function () {
    return view('main');
});

include base_path('routes/web.php');

TAO::routes();