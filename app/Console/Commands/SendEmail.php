<?php

namespace App\Console\Commands;

use App\Utilities\NagiosServicelist;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meters:email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends scheduled emails';

    /**
     * Execute the console command.
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function handle(): void
    {
        Mail::to(config('meters.alert_email_recipients'))
                ->send(new DailyAlertStatus(new NagiosServicelist()));
    }
}
