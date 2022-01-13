<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

use Illuminate\Http\Request;

$router->get('/v1', function () use ($router) {
    return "Email microservice API" . " (". $router->app->version() . ")";
});

$router->group(['prefix' => 'v1', 'middleware' => 'JsonRequestMiddleware'], function() use ($router) {

    $router->post('/message', 'SendMailController@sendMessage');

    $router->post('/template', 'SendMailController@sendTemplate');

    $router->get('/test', function () use ($router) {

        $data = request()->all();

        return response()->json([
            'request' => $data,
            'response' => [
                'result' => 'Success.'
            ]
        ]);

        return "Success.";
    });

});

