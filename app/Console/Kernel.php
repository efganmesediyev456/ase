<?php

namespace App\Console;

use App\Console\Commands\AseLogic;
use App\Console\Commands\AssignCourierCommand;
use App\Console\Commands\AutoCourierAssign;
use App\Console\Commands\AzerPoct;
use App\Console\Commands\Campaign;
use App\Console\Commands\AzeriExpress;
use App\Console\Commands\AzerPoctSend;
use App\Console\Commands\CheckKapitalTransaction;
use App\Console\Commands\Debt;
use App\Console\Commands\DebtNotification;
use App\Console\Commands\FilialUpdate;
use App\Console\Commands\IntegrationSendStatuses;
use App\Console\Commands\Kargomat;
use App\Console\Commands\NoDeclarationsResend;
use App\Console\Commands\NotPaidNotification;
use App\Console\Commands\PaymentError;
use App\Console\Commands\SuretSend;
use App\Console\Commands\CancelDepesh;
use App\Console\Commands\CarriersAdd;
use App\Console\Commands\CarriersAir;
use App\Console\Commands\CarriersCheck;
use App\Console\Commands\CarriersCheck2;
use App\Console\Commands\CarriersDeclarations;
use App\Console\Commands\CarriersDelete;
use App\Console\Commands\CarriersGoods;
use App\Console\Commands\CarriersList;
use App\Console\Commands\CarriersTrackAdd;
use App\Console\Commands\CarriersTrackUpdate;
use App\Console\Commands\CarriersUpdate;
use App\Console\Commands\CarriersNoDec;
use App\Console\Commands\ClearInvoices;
use App\Console\Commands\ClearRequests;
use App\Console\Commands\Currency;
use App\Console\Commands\Declaration;
use App\Console\Commands\Depesh;
use App\Console\Commands\House;
use App\Console\Commands\Payment;
use App\Console\Commands\SendFailedRequests;
use App\Console\Commands\SendNotification;
use App\Console\Commands\SendTracksToCustoms;
use App\Console\Commands\SyncContainerStatuses;
use App\Console\Commands\Test;
use App\Console\Commands\DepeshStart;
use App\Console\Commands\UkraineExpress;
use App\Console\Commands\UkraineExpress2;
use App\Console\Commands\CarriersTrackStatusUpdate;
use App\Console\Commands\YeniPoct;
use App\Services\AzeriExpress\AzeriExpressService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        SendNotification::class,
        AssignCourierCommand::class,
        House::class,
        UkraineExpress::class,
        UkraineExpress2::class,
        AzerPoct::class,
        CarriersAdd::class,
        CarriersTrackAdd::class,
        CarriersTrackUpdate::class,
        CarriersCheck::class,
        CarriersCheck2::class,
        CarriersDelete::class,
        CarriersUpdate::class,
        CarriersList::class,
        CarriersGoods::class,
        CarriersDeclarations::class,
        CarriersTrackStatusUpdate::class,
        CarriersNoDec::class,
        CarriersAir::class,
        ClearInvoices::class,
        ClearRequests::class,
        Depesh::class,
        CancelDepesh::class,
        Declaration::class,
        AseLogic::class,
        Campaign::class,
        Payment::class,
        PaymentError::class,
        Currency::class,
        Test::class,
        AutoCourierAssign::class,
        DepeshStart::class,
        SendTracksToCustoms::class,
        SendFailedRequests::class,
        AzeriExpress::class,
        YeniPoct::class,
        AzerPoctSend::class,
        SuretSend::class,
        FilialUpdate::class,
        NoDeclarationsResend::class,
        NotPaidNotification::class,
        DebtNotification::class,
        Debt::class,
        Kargomat::class,
        SyncContainerStatuses::class,
        IntegrationSendStatuses::class,
        CheckKapitalTransaction::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        $schedule->call(function(){
            $service = new AzeriExpressService();
            $service->getBranches();
        })->everyMinute();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
