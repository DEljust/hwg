<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => [
    'app' => 'Library API',
    'docs' => 'https://github.com/DEljust/hwg#readme',
]);
