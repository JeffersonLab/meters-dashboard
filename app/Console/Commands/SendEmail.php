<?php

namespace App\Console\Commands;

use App\Alerts\MeterAlertRepository;
use App\Mail\ConsumptionAlert;
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
     *
     * @throws \Exception
     */
    public function handle(): void
    {
        $meterAlertRepository = new MeterAlertRepository();
        $consumptionAlerts = $meterAlertRepository->alerts()
            ->sortBy(function ($alert, $key) {
                return $alert->meter()->epics_name;
            });
//        $mail = new App\Mail\ConsumptionAlert($consumptionAlerts);
        $sent = Mail::to('theo@jlab.org')->send(new ConsumptionAlert($consumptionAlerts));
//        Mail::to(config('meters.alert_email_recipients'))
//            ->send(new DailyAlertStatus(new NagiosServicelist));
    }
}
