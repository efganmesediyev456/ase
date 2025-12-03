<?php

namespace App\Console\Commands;

use App\Jobs\UpdateCarrierPackagePaymentStatusJob;
use App\Models\CD;
use App\Models\DebtLog;
use App\Models\Extra\Notification;
use App\Models\PayPhone;
use App\Models\Transaction;
use App\Services\Integration\UnitradeService;
use App\Services\KapitalBank\KapitalBankTxpgService;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Track;
use App\Models\Package;
use DB;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Kapital Transactions which status is pending';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
//            ->whereNotNull('source_id')
//            ->where('paid_by', 'KAPITAL')
//            ->where('type', 'PENDING')
//            ->where('created_at', '>=', '2025-07-01 00:00:00')
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {


        $tracks = Track::whereIn('tracking_code', ['EQ0000238591AZ', 'EQ0000238536AZ', 'EQ0000238532AZ', 'EQ0000238477AZ', 'EQ0000238461AZ', 'EQ0000238445AZ', 'EQ0000238444AZ', 'EQ0000238425AZ', 'EQ0000238374AZ', 'EQ0000238166AZ', 'EQ0000238147AZ', 'EQ0000238141AZ', 'EQ0000238129AZ', 'EQ0000238062AZ', 'EQ0000238059AZ', 'EQ0000237991AZ', 'EQ0000237976AZ', 'EQ0000237873AZ', 'EQ0000237744AZ', 'EQ0000237715AZ', 'EQ0000237689AZ', 'EQ0000237656AZ', 'EQ0000237607AZ', 'EQ0000237360AZ', 'EQ0000237358AZ', 'EQ0000237343AZ', 'EQ0000237341AZ', 'EQ0000237332AZ', 'EQ0000237331AZ', 'EQ0000237329AZ', 'EQ0000237318AZ', 'EQ0000237291AZ', 'EQ0000237279AZ', 'EQ0000237256AZ', 'EQ0000237244AZ', 'EQ0000237232AZ', 'EQ0000237229AZ', 'EQ0000237167AZ', 'EQ0000236908AZ', 'EQ0000236907AZ', 'EQ0000236902AZ', 'EQ0000236882AZ', 'EQ0000236571AZ', 'EQ0000236494AZ', 'EQ0000236400AZ', 'EQ0000236363AZ', 'EQ0000236160AZ', 'EQ0000235980AZ', 'EQ0000235978AZ', 'EQ0000235974AZ', 'EQ0000235973AZ', 'EQ0000235955AZ', 'EQ0000235864AZ', 'EQ0000235725AZ', 'EQ0000235719AZ', 'EQ0000235708AZ', 'EQ0000235690AZ', 'EQ0000235659AZ', 'EQ0000235645AZ', 'EQ0000235631AZ', 'EQ0000235613AZ', 'EQ0000235609AZ', 'EQ0000235561AZ', 'EQ0000235073AZ', 'EQ0000234961AZ', 'EQ0000234851AZ', 'EQ0000234847AZ', 'EQ0000234846AZ', 'EQ0000234754AZ', 'EQ0000234736AZ', 'EQ0000234731AZ', 'EQ0000234730AZ', 'EQ0000234709AZ', 'EQ0000234677AZ', 'EQ0000234643AZ', 'EQ0000234570AZ', 'EQ0000234244AZ', 'EQ0000234243AZ', 'EQ0000234228AZ', 'EQ0000234182AZ', 'EQ0000234137AZ', 'EQ0000234136AZ', 'EQ0000234100AZ', 'EQ0000234064AZ', 'EQ0000233733AZ', 'EQ0000233698AZ', 'EQ0000233685AZ', 'EQ0000233647AZ', 'EQ0000233642AZ', 'EQ0000233593AZ', 'EQ0000233562AZ', 'EQ0000233555AZ', 'EQ0000233552AZ', 'EQ0000233411AZ', 'EQ0000233398AZ', 'EQ0000233181AZ', 'EQ0000232921AZ', 'EQ0000232705AZ', 'EQ0000232681AZ', 'EQ0000232364AZ', 'EQ0000232328AZ', 'EQ0000232292AZ', 'EQ0000231425AZ', 'EQ0000231415AZ', 'EQ0000231394AZ', 'EQ0000231377AZ', 'EQ0000231367AZ', 'EQ0000231328AZ', 'EQ0000230313AZ', 'EQ0000230281AZ', 'EQ0000230280AZ', 'EQ0000230193AZ', 'EQ0000230191AZ', 'EQ0000230189AZ', 'EQ0000230176AZ', 'EQ0000230172AZ', 'EQ0000230156AZ', 'EQ0000229922AZ', 'EQ0000229878AZ', 'EQ0000229846AZ', 'EQ0000229572AZ', 'EQ0000229454AZ', 'EQ0000228993AZ', 'EQ0000228977AZ', 'EQ0000228523AZ', 'EQ0000228522AZ', 'EQ0000228486AZ', 'EQ0000228458AZ', 'EQ0000228453AZ', 'EQ0000228097AZ', 'EQ0000228070AZ', 'EQ0000227750AZ', 'EQ0000226506AZ', 'EQ0000226417AZ', 'EQ0000226285AZ', 'EQ0000226282AZ', 'EQ0000226086AZ', 'EQ0000226081AZ', 'EQ0000225952AZ', 'EQ0000225933AZ', 'EQ0000225926AZ', 'EQ0000225925AZ', 'EQ0000225842AZ', 'EQ0000225479AZ', 'EQ0000225447AZ', 'EQ0000225133AZ', 'EQ0000225124AZ', 'EQ0000225110AZ', 'EQ0000225086AZ', 'EQ0000225085AZ', 'EQ0000224668AZ', 'EQ0000224546AZ', 'EQ0000224538AZ', 'EQ0000224515AZ', 'EQ0000224480AZ', 'EQ0000224453AZ', 'EQ0000224428AZ', 'EQ0000224426AZ', 'EQ0000224352AZ', 'EQ0000223874AZ', 'EQ0000223858AZ', 'EQ0000223857AZ', 'EQ0000223424AZ', 'EQ0000223021AZ', 'EQ0000222939AZ', 'EQ0000222593AZ', 'EQ0000222361AZ', 'EQ0000222359AZ', 'EQ0000222358AZ', 'EQ0000222357AZ', 'EQ0000222277AZ', 'EQ0000222257AZ', 'EQ0000222255AZ', 'EQ0000222240AZ', 'EQ0000221499AZ', 'EQ0000221036AZ', 'EQ0000221035AZ', 'EQ0000221033AZ', 'EQ0000220845AZ', 'EQ0000220682AZ', 'EQ0000220293AZ', 'EQ0000220259AZ', 'EQ0000220165AZ', 'EQ0000220148AZ', 'EQ0000220145AZ', 'EQ0000220140AZ', 'EQ0000219826AZ', 'EQ0000219145AZ', 'EQ0000218512AZ', 'EQ0000218339AZ', 'EQ0000218201AZ', 'EQ0000218192AZ', 'EQ0000218161AZ', 'EQ0000218160AZ', 'EQ0000218159AZ', 'EQ0000217885AZ', 'EQ0000217867AZ', 'EQ0000217858AZ', 'EQ0000217621AZ', 'EQ0000217486AZ', 'EQ0000216357AZ', 'EQ0000216353AZ', 'EQ0000216352AZ', 'EQ0000216290AZ', 'EQ0000216229AZ', 'EQ0000215866AZ', 'EQ0000215466AZ', 'EQ0000215447AZ', 'EQ0000215104AZ', 'EQ0000215103AZ', 'EQ0000214813AZ', 'EQ0000214327AZ', 'EQ0000213426AZ', 'EQ0000213419AZ', 'EQ0000213373AZ', 'EQ0000211837AZ', 'EQ0000211806AZ', 'EQ0000211736AZ', 'EQ0000211724AZ', 'EQ0000211714AZ', 'EQ0000211682AZ', 'EQ0000211596AZ', 'EQ0000211251AZ', 'EQ0000210881AZ', 'EQ0000210794AZ', 'EQ0000210793AZ', 'EQ0000210397AZ', 'EQ0000210375AZ', 'EQ0000210144AZ', 'EQ0000210134AZ', 'EQ0000210132AZ', 'EQ0000210026AZ', 'EQ0000209793AZ', 'EQ0000209672AZ', 'EQ0000209424AZ', 'EQ0000209361AZ', 'EQ0000208925AZ', 'EQ0000208560AZ', 'EQ0000208422AZ', 'EQ0000208204AZ', 'EQ0000207705AZ', 'EQ0000207648AZ', 'EQ0000207640AZ', 'EQ0000207636AZ', 'EQ0000206784AZ', 'EQ0000206736AZ', 'EQ0000206734AZ', 'EQ0000205746AZ', 'EQ0000205679AZ', 'EQ0000205187AZ', 'EQ0000203970AZ', 'EQ0000201345AZ', 'EQ0000201205AZ', 'EQ0000201173AZ', 'EQ0000201124AZ', 'EQ0000200631AZ', 'EQ0000200328AZ', 'EQ0000200115AZ', 'EQ0000200079AZ', 'EQ0000199624AZ', 'EQ0000198387AZ', 'EQ0000196676AZ', 'EQ0000195938AZ', 'EQ0000195931AZ', 'EQ0000195472AZ', 'EQ0000195398AZ', 'EQ0000194960AZ', 'EQ0000194957AZ', 'EQ0000193776AZ', 'EQ0000193606AZ', 'EQ0000192911AZ', 'EQ0000192167AZ', 'EQ0000192090AZ', 'EQ0000191570AZ', 'EQ0000191569AZ', 'EQ0000191566AZ', 'EQ0000191565AZ', 'EQ0000191564AZ', 'EQ0000191561AZ', 'EQ0000191218AZ', 'EQ0000189060AZ', 'EQ0000189036AZ', 'EQ0000188779AZ', 'EQ0000188410AZ', 'EQ0000188401AZ', 'EQ0000187815AZ', 'EQ0000187814AZ', 'EQ0000187788AZ', 'EQ0000187787AZ', 'EQ0000187316AZ', 'EQ0000185144AZ', 'EQ0000184282AZ', 'EQ0000184096AZ', 'EQ0000183155AZ', 'EQ0000183090AZ', 'EQ0000179640AZ', 'EQ0000178661AZ', 'EQ0000178654AZ', 'EQ0000176416AZ', 'EQ0000176233AZ', 'EQ0000176139AZ', 'EQ0000175236AZ', 'EQ0000175219AZ', 'EQ0000174663AZ', 'EQ0000174525AZ', 'EQ0000172986AZ', 'EQ0000171203AZ', 'EQ0000170802AZ', 'EQ0000170747AZ', 'EQ0000170703AZ', 'EQ0000170022AZ', 'EQ0000169510AZ', 'EQ0000169483AZ', 'EQ0000169302AZ', 'EQ0000168644AZ', 'EQ0000168643AZ', 'EQ0000168035AZ', 'EQ0000165449AZ', 'EQ0000164953AZ', 'EQ0000164822AZ', 'EQ0000164041AZ', 'EQ0000163035AZ', 'EQ0000163015AZ', 'EQ0000162687AZ', 'EQ0000162406AZ', 'EQ0000161624AZ', 'EQ0000160486AZ', 'EQ0000160064AZ', 'EQ0000157103AZ', 'EQ0000156487AZ', 'EQ0000156202AZ', 'EQ0000155173AZ', 'EQ0000151654AZ', 'EQ0000151632AZ', 'EQ0000151576AZ', 'EQ0000150612AZ', 'EQ0000150598AZ', 'EQ0000149643AZ', 'EQ0000149610AZ', 'EQ0000148243AZ', 'EQ0000146690AZ', 'EQ0000145671AZ', 'EQ0000145635AZ', 'EQ0000144375AZ', 'EQ0000144238AZ', 'EQ0000143647AZ', 'EQ0000142800AZ', 'EQ0000141078AZ', 'EQ0000140627AZ', 'EQ0000137846AZ', 'EQ0000137836AZ', 'EQ0000137007AZ', 'EQ0000136418AZ', 'EQ0000135997AZ', 'EQ0000133764AZ', 'EQ0000132432AZ', 'EQ0000131333AZ', 'EQ0000130749AZ', 'EQ0000129735AZ', 'EQ0000128337AZ', 'EQ0000128092AZ', 'EQ0000125071AZ', 'EQ0000121280AZ', 'EQ0000120382AZ', 'EQ0000120352AZ', 'EQ0000118301AZ', 'EQ0000114756AZ', 'EQ0000114747AZ', 'EQ0000114004AZ', 'EQ0000113011AZ', 'EQ0000112986AZ', 'EQ0000111530AZ', 'EQ0000111316AZ', 'EQ0000111002AZ', 'EQ0000110729AZ', 'EQ0000110107AZ', 'EQ0000104198AZ', 'EQ0000102827AZ', 'EQ0000084623AZ', 'EQ0000082826AZ', 'EQ0000054476AZ'])->get();


        foreach ($tracks as $track) {
            Notification::sendTrack($track->id, 'TAOBAO_SENT_PAYMENT');
            $this->info('Sent TAOBAO payment notification for track ' . $track->id);
        }

        exit;



        DB::statement("SET time_zone = '+04:00'");

        $tracks = DB::table('tracks')
            ->select(
                'id',
                'tracking_code',
                'customs_at',
                DB::raw('TIMESTAMPDIFF(SECOND, customs_at, NOW()) AS diff_seconds'),
                DB::raw('TIMESTAMPDIFF(SECOND, customs_at, NOW()) / 3600 AS diff_hours'),
                'debt_price',
                DB::raw("
            CASE
                WHEN (TIMESTAMPDIFF(SECOND, customs_at, NOW()) / 3600) <= 24
                    THEN 0
                ELSE
                    3 +
                    (
                        FLOOR(
                            (TIMESTAMPDIFF(SECOND, customs_at, NOW()) / 3600 - 24) / 24
                        ) * 0.50
                    )
            END
        AS calculated_debt")
            )
            ->where('paid_debt', 0)
            ->where('debt_price', '>', 0)
            ->get();


        foreach ($tracks as $track) {
            $track2 = Track::withTrashed()->find($track->id);
            $track2->debt_price = $track->calculated_debt;
            $track2->save();


            DebtLog::create([
                'custom_id' => $track2->custom_id,
                'price' => $track->debt_price,
                'after_price' => $track->calculated_debt,
            ]);


            $this->info($track->id . " " . $track->calculated_debt);
        }

//        dd($tracks);

        exit;


        $kapitalBankTxpgService = new KapitalBankTxpgService();
        $botToken = "7784139238:AAGfstOZANbUgTV3hYKV8Xua8xQ_eJs5_wU";
        $website = "https://api.telegram.org/bot" . $botToken;
        $chatId = "-1002397303546";

        Transaction::query()
            ->whereNotNull('source_id')
            ->where('paid_by', 'KAPITAL')
            ->where('type', 'PENDING')
            ->where('paid_for', 'COURIER_TRACK_OZON_DELIVERY')
            ->whereBetween('created_at', ['2025-07-01 00:00:00', now()])
            ->orderBy('id')
            ->chunk(100, function ($transactions) use ($kapitalBankTxpgService, $website, $chatId) {


                foreach ($transactions as $transaction) {
                    try {
                        $orderId = $transaction->source_id;
                        $orderStatus = $kapitalBankTxpgService->getOrderStatus($orderId);

                        if (!isset($orderStatus['order']['status'])) {
                            throw new Exception("Error");
                        }

                        $status = $orderStatus['order']['status'];
                        $transaction->extra_data = json_encode($orderStatus);
                        $transaction->request_all = json_encode($orderStatus);

                        $isFullyPaid = ($status === 'FullyPaid');
                        $isFailed = in_array($status, ['Declined', 'Cancelled', 'Expired', 'Declined', 'Pending']);

//                        if (!$isFullyPaid && !$isFailed) {
//                            $transaction->type = $status;
//                            $transaction->save();
//                            continue;
//                        }

                        switch ($transaction->paid_for) {
                            case 'COURIER_DELIVERY':
                                $this->processCourierDelivery($transaction, $status, $orderStatus);
                                break;
                            case 'MARKET':
                                $this->processMarketPayment($transaction, $status, $orderStatus);
                                break;
                            case 'PACKAGE':
                                $this->processPackage($transaction, $status, $orderStatus);
                                break;
                            case 'TRACK_DELIVERY':
                                $this->processTrackDelivery($transaction, $status, $orderStatus);
                                break;
                            case 'PACKAGE_DEBT':
                                $this->processPackageDebt($transaction, $status, $orderStatus);
                                break;
                            case 'TRACK_DEBT':
                                $this->processTrackDebt($transaction, $status, $orderStatus);
                                break;
                            case 'COURIER_TRACK_OZON_DELIVERY':
                                $this->processCourierTrackOzonDeliveryPayment($transaction, $status, $orderStatus);
                                break;
                            default:
                                $transaction->type = $status;
                                $transaction->save();
                        }

                    } catch (Exception $e) {
                        $errorMsg = "‼️ AseShop Hata: " . $e->getMessage();
                        file_get_contents($website . "/sendMessage?chat_id=" . $chatId . "&text=" . urlencode($errorMsg));
                        $this->error($errorMsg);
                    }
                }
            });
    }

    private function processCourierDelivery($transaction, $status, $orderStatus)
    {

        $delivery = CD::withTrashed()->find($transaction->custom_id);
        if (!$delivery) return;

        $user = $delivery->user;
        $logType = 'courier';

        if ($status === 'FullyPaid') {
            $transaction->type = 'OUT';
            $delivery->paid = 1;
            $delivery->deleted_at = null;
            $delivery->recieved = true;
            $delivery->save();

            $message = "💳 <b>{$user->full_name}</b> ({$user->customer_id}) "
                . "Kapital ile <a href='https://admin." . env('DOMAIN_NAME') . "/courier_deliveries/{$delivery->id}/info'>{$delivery->id}</a> "
                . "ID'li kuryer için <b>{$delivery->delivery_price} AZN</b> ödeme yaptı.";
        } else {
            $transaction->type = $status;
            $message = "🚫 {$user->full_name} kuryer ödemesi başarısız. Sebep: {$status}";
        }

        $transaction->save();
        sendTGMessage($message);
        $this->logPaymentResult($user->id, $logType, $status);
    }

    private function processMarketPayment($transaction, $status, $orderStatus)
    {

        $pay_phone = PayPhone::query()->find($transaction->custom_id);
        if (!$pay_phone) return;

        $logType = 'market';

        if ($status === 'FullyPaid') {
            $transaction->type = 'OUT';
            $pay_phone->status = 'success';

            $pay_phone->save();

            $message = "💳 <b>{$pay_phone->phone}</b> ({$pay_phone->phone}) "
                . "Kapital ile <a href='https://admin." . env('DOMAIN_NAME') . "/courier_deliveries/{$pay_phone->id}/info'>{$pay_phone->id}</a> "
                . "ID'li kuryer için <b>{$pay_phone->amount} AZN</b> ödeme yaptı.";
        } else {
            $transaction->type = $status;
            $pay_phone->status = 'failed';
            $pay_phone->save();
            $message = "🚫 {$pay_phone->phone} market ödemesi başarısız. Sebep: {$status}";
        }

        $transaction->save();
        sendTGMessage($message);
        $this->logPaymentResult($pay_phone->id, $logType, $status);
    }

    private function processPackage($transaction, $status, $orderStatus)
    {

        $packages = Package::where('id', $transaction->custom_id)->get();
        $transactions = Transaction::where('source_id', $transaction->source_id)->get();

        foreach ($transactions as $t) {
            $t->type = ($status === 'FullyPaid') ? 'OUT' : $status;
            $t->extra_data = json_encode($orderStatus);
            $t->request_all = json_encode($orderStatus);
            $t->save();
        }

        if ($status === 'FullyPaid') {
            foreach ($packages as $package) {
                $package->paid = 1;
                $package->save();
                dispatch(new UpdateCarrierPackagePaymentStatusJob($package->custom_id));

                $message = "💳 <b>{$package->user->full_name}</b> ({$package->user->customer_id}) "
                    . "Portmanat ile <a href='https://admin." . env('DOMAIN_NAME') . "/packages/{$package->id}/edit'>{$package->tracking_code}</a> "
                    . "takip kodu için <b>{$package->delivery_manat_price_discount} AZN</b> ödeme yaptı.";

                sendTGMessage($message);
            }
        }

        $this->logPaymentResult($transaction->user_id, 'package', $status);
    }

    private function processTrackDelivery($transaction, $status, $orderStatus)
    {
        $track = Track::find($transaction->custom_id);
        if (!$track) return;

        $logType = 'track';

        if ($status === 'FullyPaid') {
            $transaction->type = 'OUT';
            $track->paid = 1;
            $track->bot_comment = 'Web link pay';
            $track->save();

            $message = "💳 <b>{$track->fullname}</b> "
                . "Portmanat ile <a href='https://admin." . env('DOMAIN_NAME') . "/tracks/q={$track->tracking_code}'>{$track->tracking_code}</a> "
                . "takip kodu için <b>{$track->delivery_price_azn1} AZN</b> ödeme yaptı.";
        } else {
            $transaction->type = $status;
            $message = "🚫 {$track->fullname} takip ödemesi başarısız. Sebep: {$status}";
        }

        $transaction->save();
        sendTGMessage($message);
        $this->logPaymentResult($track->id, $logType, $status);
    }

    private function processPackageDebt($transaction, $status, $orderStatus)
    {
        $package = Package::find($transaction->custom_id);
        if (!$package) return;

        $user = $package->user;
        $logType = 'package_debt';

        if ($status === 'FullyPaid') {
            $transaction->type = 'OUT';
            $transaction->debt = 1;
            $package->paid_debt = 1;
            $package->bot_comment = 'Web link pay';
            $package->save();

            $message = "💳 <b>{$user->full_name}</b> ({$user->customer_id}) "
                . "Kapital ile <a href='https://admin." . env('DOMAIN_NAME') . "/packages/{$package->id}/edit'>{$package->tracking_code}</a> "
                . "takip kodu için <b>{$package->debt_price} AZN</b> borç ödemesi yaptı.";
        } else {
            $transaction->type = $status;
            $message = "🚫 {$user->full_name} paket borç ödemesi başarısız. Sebep: {$status}";
        }

        $transaction->save();
        sendTGMessage($message);
        $this->logPaymentResult($user->id, $logType, $status);
    }

    private function processTrackDebt($transaction, $status, $orderStatus)
    {
        $track = Track::find($transaction->custom_id);
        if (!$track) return;

        $logType = 'track_debt';

        if ($status === 'FullyPaid') {
            $transaction->type = 'OUT';
            $transaction->debt = 1;
            $track->paid_debt = 1;
            $track->bot_comment = 'Web link pay';
            $track->save();

            $message = "💳 <b>{$track->fullname}</b> "
                . "Kapital ile <a href='https://admin." . env('DOMAIN_NAME') . "/tracks/q={$track->tracking_code}'>{$track->tracking_code}</a> "
                . "takip kodu için <b>{$track->debt_price} AZN</b> borç ödemesi yaptı.";
        } else {
            $transaction->type = $status;
            $message = "🚫 {$track->fullname} takip borç ödemesi başarısız. Sebep: {$status}";
        }

        $transaction->save();
        sendTGMessage($message);
        $this->logPaymentResult($track->id, $logType, $status);
    }


    public function processCourierTrackOzonDeliveryPayment($transaction, $status, $orderStatus)
    {
        $cd = CD::find($transaction->custom_id);
        if (!$cd) return;

        $logType = 'courier_track_ozon_delivery';
        $transaction->type = 'OUT';


        if ($status === 'FullyPaid') {
            $cd->paid = 1;
            $cd->save();

            $message = "💳 <b>{$cd->id} id nomreli courier delivery </b> "
                . "Kapital ile  "
                . " <b> {$transaction->amount} AZN</b> çatdırılma xidməti etdi.";
        } else {
            $transaction->type = $status;
            $message = "🚫 {{$cd->id} id nomreli courier delivery çatdırılma xidməti ödənişində səhvik oldu. Səbəb: {$status}";
        }

        $transaction->save();
        sendTGMessage($message);
        $this->logPaymentResult($cd->id . ' id courier delivery ', $logType, $status);
    }

    private function logPaymentResult($userId, $paymentType, $status)
    {
        $result = ($status === 'FullyPaid') ? 'success' : 'error ' . $status;
        $logMessage = Carbon::now() . " {$userId} {$paymentType}_callback {$result}\n";

        file_put_contents('/var/log/ase_portmanat.log', $logMessage, FILE_APPEND);
        $this->info("{$status} - {$paymentType}");
    }
}
