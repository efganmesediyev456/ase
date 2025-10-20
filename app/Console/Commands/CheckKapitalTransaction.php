<?php

namespace App\Console\Commands;

use App\Jobs\UpdateCarrierPackagePaymentStatusJob;
use App\Models\CD;
use App\Models\PayPhone;
use App\Models\Transaction;
use App\Services\KapitalBank\KapitalBankTxpgService;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Track;
use App\Models\Package;

class CheckKapitalTransaction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:transactions';

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
        $kapitalBankTxpgService = new KapitalBankTxpgService();
        $botToken = "7784139238:AAGfstOZANbUgTV3hYKV8Xua8xQ_eJs5_wU";
        $website = "https://api.telegram.org/bot" . $botToken;
        $chatId = "-1002397303546";

        Transaction::query()
            ->whereNotNull('source_id')
            ->where('paid_by', 'KAPITAL')
            ->where('type', 'PENDING')
            ->whereBetween('created_at', ['2025-07-01 00:00:00', now()->subMinutes(5)])
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
                        $isFailed = in_array($status, ['Declined', 'Cancelled', 'Expired', 'Declined','Pending']);

                        if (!$isFullyPaid && !$isFailed) {
                            $transaction->type = $status;
                            $transaction->save();
                            continue;
                        }

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

    private function logPaymentResult($userId, $paymentType, $status)
    {
        $result = ($status === 'FullyPaid') ? 'success' : 'error ' . $status;
        $logMessage = Carbon::now() . " {$userId} {$paymentType}_callback {$result}\n";

        file_put_contents('/var/log/ase_portmanat.log', $logMessage, FILE_APPEND);
        $this->info("{$status} - {$paymentType}");
    }
}
