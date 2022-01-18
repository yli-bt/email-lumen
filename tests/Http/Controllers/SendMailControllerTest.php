<?php

namespace Http\Controllers;

use App\Http\Controllers\SendMailController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Http\Request;
use PHPUnit\Framework\TestCase;
use SendGrid\Mail\TypeException;


class SendMailControllerTest extends TestCase
{
    /**
     * @throws TypeException
     * @throws ValidationException
     */
    public function testSendValidMessage() {

        $request = Request::create('/message', 'POST',[
        ]);


        $controller = new SendMailController();

        $response = $controller->sendMessage($request);

        $this->assertEquals(200, $response->getStatusCode());
    }
}
