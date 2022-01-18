<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;

use SendGrid\Mail\Mail;
use SendGrid\Mail\MailSettings;
use SendGrid\Mail\SandBoxMode;
use SendGrid\Mail\TypeException;

abstract class SendGridJob extends Job
{

    protected $data;
    protected $sendgrid;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
        $this->sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    abstract public function handle();

    protected static function getSandboxEnabledMailSettings() {

        /* create a mail settings object with sandbox mode enabled */
        $mailSettings= new MailSettings();
        $sandboxMode = new SandBoxMode();
        $sandboxMode->setEnable(true);
        $mailSettings->setSandboxMode($sandboxMode);

        return $mailSettings;
    }
}
