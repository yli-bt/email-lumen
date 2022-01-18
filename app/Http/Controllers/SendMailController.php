<?php

namespace App\Http\Controllers;

use App\Jobs\MessageJob;

use App\Jobs\TemplateJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

use SendGrid\Mail\Mail;
use SendGrid\Mail\MailSettings;
use SendGrid\Mail\SandBoxMode;
use SendGrid\Mail\TypeException;

class SendMailController extends Controller
{

//    private $sendgrid;

    private const SEND_VALIDATOR = [
        'from' => 'required',
        'from.email' => 'required|email',
        'from.name' => 'string',
        'to' => 'array|required',
        'to.*.email' => 'required|email',
        'to.*.name' => 'string',
        'message' => 'required',
        'message.text/plain' => 'required|string',
        'message.subject' => 'string',
        'sandbox' => 'boolean'
    ];

//    public function __construct() {
//        $this->sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
//    }

    /**
     * @throws ValidationException
     */
    public function queueMessage(Request $request) : JsonResponse {
        $this->validate($request, self::SEND_VALIDATOR);

        $data = $request->all();

        $dispatchResult = dispatch(new MessageJob($data));

        Log::notice('Dispatching MessageJob', ['dispatchResult' => $dispatchResult]);

        return response()->json([
            'result' => 'MessageJob dispatched',
            'time' => date(DATE_ATOM)
        ]);
    }

    /**
     * @throws TypeException
     * @throws ValidationException
     */
    public function queueTemplate(Request $request): JsonResponse
    {
        $this->validate($request, self::SEND_VALIDATOR);

        $data = $request->all();

        $dispatchResult = dispatch(new TemplateJob($data));

        Log::notice('Dispatching TemplateJob', ['dispatchResult' => $dispatchResult]);

        return response()->json([
            'result' => 'TemplateJob dispatched',
            'time' => date(DATE_ATOM)
        ]);
    }

//    private static function getSandboxEnabledMailSettings() {
//
//        /* create a mail settings object with sandbox mode enabled */
//        $mailSettings= new MailSettings();
//        $sandboxMode = new SandBoxMode();
//        $sandboxMode->setEnable(true);
//        $mailSettings->setSandboxMode($sandboxMode);
//
//        return $mailSettings;
//    }

}
