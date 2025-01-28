<?php

use App\Http\Controllers\TelegramController;

Route::get('', ['as' => 'admin.dashboard', function () {
	$content = 'Define your dashboard here.';
	return AdminSection::view($content, 'Dashboard');
}]);

Route::get('information', ['as' => 'admin.information', function () {
	$content = 'Define your information here.';
	return AdminSection::view($content, 'Information');
}]);

Route::get('message', ['as' => 'admin.message', function () {
    return AdminSection::view(view('admin.message'), 'Массовая рассылка');
}]);

