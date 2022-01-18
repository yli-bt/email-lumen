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

    $router->post('/message', 'SendMailController@queueMessage');

    $router->post('/template', 'SendMailController@queueTemplate');

    $router->get('/test', function () use ($router) {

        $data = request()->all();
        $response_data = [
            'request' => $data,
            'response' => [
                'result' => 'Success.'
            ],
            'test_env_var' => getenv('TEST_ENV_VAR')
        ];

        Log::notice('Hit /test endpoint');

        return response()->json($response_data);

    });

});

