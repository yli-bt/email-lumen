<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;

use SendGrid\Mail\Mail;
use SendGrid\Mail\MailSettings;
use SendGrid\Mail\SandBoxMode;
use SendGrid\Mail\TypeException;

class MessageJob extends SendGridJob
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = $this->data;

        $email = new Mail();

        $fromName = $data['from']['name'] ?? $data['from']['email'];
        $email->setFrom($data['from']['email'], $fromName);

        $email->setSubject($data['message']['subject'] ?? '');

        $textMessage = $data['message']['text/plain'];
        $htmlMessage = $data['message']['text/html'] ?? $data['message']['text/plain'];

        $email->addContent('text/plain', $textMessage);
        $email->addContent('text/html', $htmlMessage);

        foreach ($data['to'] as $recipient) {
            $toName = $recipient['name'] ?? $recipient['email'];
            $email->addTo($recipient['email'], $toName);
        }

        if (array_key_exists('sandbox', $data) && $data['sandbox'] == true) {
            $email->setMailSettings(self::getSandboxEnabledMailSettings());
        }

        try {
            $sendgridResponse = $this->sendgrid->send($email);

            $sendgridResponseData = [
                'sendgridStatusCode' => $sendgridResponse->statusCode(),
                'sendgridHeaders' => $sendgridResponse->headers(),
                'sendgridBody' => $sendgridResponse->body(),
            ];
        } catch (Exception $e) {
            echo 'Caught exception: '. $e->getMessage() ."\n";
        }

        $data = array_merge($data, [
            'result' => 'Called sendMessage endpoint',
            'sendgridResponse' => $sendgridResponseData,
            'time' => date(DATE_ATOM)
        ]);

        Log::notice('MessageJob processed.', $data);
        Log::notice("SendGrid response", $sendgridResponseData);
    }
}
