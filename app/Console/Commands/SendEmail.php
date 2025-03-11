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
        if ($consumptionAlerts->isNotEmpty()){
            $sent = Mail::to(config('meters.alert_email_recipients'))
                ->send(new ConsumptionAlert($consumptionAlerts));
            $this->line('email sent');    
        }else{
            $this->line('no alerts to send out via email');
        }            
    }
}
