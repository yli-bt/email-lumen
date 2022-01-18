<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;

use SendGrid\Mail\Mail;
use SendGrid\Mail\MailSettings;
use SendGrid\Mail\SandBoxMode;
use SendGrid\Mail\TypeException;

class TemplateJob extends SendGridJob
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = $this->data;

        $fromEmail = $data['from']['email'];
        $fromName = $data['from']['name'] ?? $fromEmail;

        $subjectTemplate = $data['message']['subject'];
        $plainTemplate = $data['message']['text/plain'];
        $htmlTemplate = $data['message']['text/html'] ?? $plainTemplate;

        $sentMessages = [];

        $m = new \Mustache_Engine();

        foreach ($data['to'] as $recipient) {

            $expandedSubject = $m->render($subjectTemplate, $recipient);
            $expandedTextPlainMessage = $m->render($plainTemplate, $recipient);
            $expandedTextHtmlMessage = $m->render($htmlTemplate, $recipient);

            $email = new Mail();

            if (array_key_exists('sandbox', $data) && $data['sandbox'] == true) {
                $email->setMailSettings(self::getSandboxEnabledMailSettings());
            }

            $email->setFrom($fromEmail, $fromName);
            $email->addTo($recipient['email'], $recipient['name']);

            $email->setSubject($expandedSubject);
            $email->addContent('text/plain', $expandedTextPlainMessage);
            $email->addContent('text/html', $expandedTextHtmlMessage);

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

            $sentMessages[] = [
                'to' => $recipient,
                'subject' => $expandedSubject,
                'text/plain' => $expandedTextPlainMessage,
                'text/html' => $expandedTextHtmlMessage
            ];

        }

        Log::notice('TemplateJob processed.', $data);
        Log::notice("Messages were:", $sentMessages);
        Log::notice("SendGrid response", $sendgridResponseData);

    }
}
