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

    private $sendgrid;

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

    public function __construct() {
        $this->sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
    }

//    /**
//     * @throws TypeException
//     * @throws ValidationException
//     */
//    public function sendMessage(Request $request): JsonResponse
//    {
////        $this->validate($request, self::SEND_VALIDATOR);
//
//        $data = $request->all();
//
//        $email = new Mail();
//
//        $fromName = $data['from']['name'] ?? $data['from']['email'];
//        $email->setFrom($data['from']['email'], $fromName);
//
//        $email->setSubject($data['message']['subject'] ?? '');
//
//        $textMessage = $data['message']['text/plain'];
//        $htmlMessage = $data['message']['text/html'] ?? $data['message']['text/plain'];
//
//        $email->addContent('text/plain', $textMessage);
//        $email->addContent('text/html', $htmlMessage);
//
//        foreach ($data['to'] as $recipient) {
//            $toName = $recipient['name'] ?? $recipient['email'];
//            $email->addTo($recipient['email'], $toName);
//        }
//
//        if (array_key_exists('sandbox', $data) && $data['sandbox'] == true) {
//            $email->setMailSettings(self::getSandboxEnabledMailSettings());
//        }
//
//        try {
//            $sendgridResponse = $this->sendgrid->send($email);
//
//            $sendgridResponseData = [
//                'sendgridStatusCode' => $sendgridResponse->statusCode(),
//                'sendgridHeaders' => $sendgridResponse->headers(),
//                'sendgridBody' => $sendgridResponse->body(),
//            ];
//        } catch (Exception $e) {
//            echo 'Caught exception: '. $e->getMessage() ."\n";
//        }
//
//        $data = array_merge($data, [
//            'result' => 'Called sendMessage endpoint',
//            'sendgridResponse' => $sendgridResponseData
//        ]);
//
//        return response()->json($data);
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

    private static function getSandboxEnabledMailSettings() {

        /* create a mail settings object with sandbox mode enabled */
        $mailSettings= new MailSettings();
        $sandboxMode = new SandBoxMode();
        $sandboxMode->setEnable(true);
        $mailSettings->setSandboxMode($sandboxMode);

        return $mailSettings;
    }

}
