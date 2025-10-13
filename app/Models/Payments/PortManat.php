<?php

namespace App\Models\Payments;

use App\Models\Package;
use App\Models\Transaction;
use Illuminate\Http\Request;

class PortManat
{
    private $mainUrl = 'https://psp.mps.az/process';
    //private $mainUrlCD = 'http://dev.aseshop.az/portmanat/cd_callback'; // TEST
    //private $mainUrl = 'http://dev.aseshop.az/portmanat/callback'; // TEST

    private $client_ip = null;

    private $service_id = null;

    private $hash;

    private $client_rrn;

    private $amount;

    private $secret_key;

    public function __construct()
    {
        ## Kullanıcının IP adresi
        if (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        } elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else {
            $ip = $_SERVER["REMOTE_ADDR"];
        }

        $this->service_id = config('services.portmanat.service_id');
        $this->secret_key = config('services.portmanat.key');
        $this->client_ip = env('APP_ENV') == 'local' ? '82.102.16.142' : $ip;
    }

    public function generateForm(Package $package)
    {
        if (!$package || !isset($package->id)) {
            return null;
        }

        if ($package && $package->status != 2 && $package->status != 8) {
            return null;
        }

        if ($package && $package->paid) {
            return null;
        }

        $action_adr = env('APP_URL') . 'portmanat/callback';//$this->mainUrl;

        $this->client_rrn = $package->id . "_" . uniqid();
        $this->amount = env('APP_ENV') == 'local' ? 0.2 : $package->delivery_manat_price_discount;

        $this->hash = hash_hmac('sha256', $this->service_id . $this->client_rrn . $this->amount, $this->secret_key);

        $args = [
            'service_id' => $this->service_id,
            'client_rrn' => $this->client_rrn,
            'amount' => $this->amount,
            'client_ip' => $this->client_ip,
            'hash' => $this->hash,
        ];

        $args_array = [];
        foreach ($args as $key => $value) {
            $args_array[] = '<input type="hidden" name="' . trim($key) . '" value="' . trim($value) . '" />';
        }

        return view('front.widgets.portmanat', compact('action_adr', 'args_array', 'args'));
    }

    public function generateHash($client_rrn, $amount)
    {
        return hash_hmac('sha256', $this->service_id . $client_rrn . $amount, $this->secret_key);
    }

    public function generateForm2($cd = NULL,$track = NULL)
    {
        $action_local_adr = env('APP_URL') . '/portmanat/callback';//$this->mainUrl;
        //$action_local_adr = $this->mainUrl;// TEST
        $action_portmanat_adr = $this->mainUrl;

        if ($cd) {
            $action_local_adr = env('APP_URL') . '/portmanat/cd_callback';//$this->mainUrl;
            //$action_portmanat_adr = env('APP_URL').'/portmanat/cd_callback';//$this->mainUrl;
        }
        if ($track) {
            $action_local_adr = env('APP_URL') . '/portmanat/tr_callback';//$this->mainUrl;
            //$action_portmanat_adr = env('APP_URL').'/portmanat/tr_callback';//$this->mainUrl;
        }
        //$action_adr = $this->mainUrl;

	if($track) {
            $this->client_rrn = 'tr_'.$track->id.'_'.uniqid();
            $this->amount = $track->delivery_price_azn1;
	} else if($cd) {
            $this->client_rrn = 'cd_'.$cd->id.'_'.uniqid();
            $this->amount = $cd->delivery_price;
	} else {
            $this->client_rrn = uniqid();
            $this->amount = 0;
	}

        $this->hash = hash_hmac('sha256', $this->service_id . $this->client_rrn . $this->amount, $this->secret_key);

        $args = [
            'service_id' => $this->service_id,
            'uid' => $this->client_rrn,
            'client_rrn' => $this->client_rrn,
            'amount' => $this->amount,
            'client_ip' => $this->client_ip,
            'hash' => $this->hash,
        ];

        

        //$args['psp_rrn']='pass398wpd31'; // TEST

        $args_array = [];
        foreach ($args as $key => $value) {
            $args_array[] = '<input type="hidden" id="' . trim($key) . '" name="' . trim($key) . '" value="' . trim($value) . '" />';
        }

        return view('front.widgets.portmanat', compact('action_local_adr', 'action_portmanat_adr', 'args_array', 'args','cd','track'));
    }


    public function generateFormPackageDebt($cd = NULL,$track = NULL)
    {

        $action_local_adr = env('APP_URL') . '/portmanat/callback';//$this->mainUrl;
        //$action_local_adr = $this->mainUrl;// TEST
        $action_portmanat_adr = $this->mainUrl;
        if ($cd) {
            $action_local_adr = env('APP_URL') . '/portmanat/cd_callback';//$this->mainUrl;
            //$action_portmanat_adr = env('APP_URL').'/portmanat/cd_callback';//$this->mainUrl;
        }
        if ($track) {
            $action_local_adr = env('APP_URL') . '/portmanat/tr_callback';//$this->mainUrl;
            //$action_portmanat_adr = env('APP_URL').'/portmanat/tr_callback';//$this->mainUrl;
        }
        //$action_adr = $this->mainUrl;

        if($track) {
            $this->client_rrn = 'tr_'.$track->id.'_'.uniqid();
            $this->amount = $track->debt_price;

        } else if($cd) {
            $this->client_rrn = 'cd_'.$cd->id.'_'.uniqid();
            $this->amount = $cd->debt_price;
        } else {
            $this->client_rrn = uniqid();
            $this->amount = 0;
        }
        
        $this->client_rrn = $this->client_rrn.'_packagedebt';

        $this->hash = hash_hmac('sha256', $this->service_id . $this->client_rrn . $this->amount, $this->secret_key);

        $args = [
            'service_id' => $this->service_id,
            'uid' => $this->client_rrn,
            'client_rrn' => $this->client_rrn,
            'amount' => $this->amount,
            'client_ip' => $this->client_ip,
            'hash' => $this->hash,
        ];
        //$args['psp_rrn']='pass398wpd31'; // TEST

        $args_array = [];
        foreach ($args as $key => $value) {
            $args_array[] = '<input type="hidden" id="' . trim($key) . '" name="' . trim($key) . '" value="' . trim($value) . '" />';
        }

        return view('front.widgets.portmanat-package-debt', compact('action_local_adr', 'action_portmanat_adr', 'args_array', 'args','cd','track'));
    }

    public function generateFormDebt($cd = NULL,$track = NULL)
    {

        $action_local_adr = env('APP_URL') . '/portmanat/callback';//$this->mainUrl;
        //$action_local_adr = $this->mainUrl;// TEST
        $action_portmanat_adr = $this->mainUrl;
        if ($cd) {
            $action_local_adr = env('APP_URL') . '/portmanat/cd_callback';//$this->mainUrl;
            //$action_portmanat_adr = env('APP_URL').'/portmanat/cd_callback';//$this->mainUrl;
        }
        if ($track) {
            $action_local_adr = env('APP_URL') . '/portmanat/tr_callback';//$this->mainUrl;
            //$action_portmanat_adr = env('APP_URL').'/portmanat/tr_callback';//$this->mainUrl;
        }
        //$action_adr = $this->mainUrl;

        if($track) {
            $this->client_rrn = 'tr_'.$track->id.'_'.uniqid();
            $this->amount = $track->debt_price;

        } else if($cd) {
            $this->client_rrn = 'cd_'.$cd->id.'_'.uniqid();
            $this->amount = $cd->debt_price;
        } else {
            $this->client_rrn = uniqid();
            $this->amount = 0;
        }

        $this->client_rrn = $this->client_rrn.'_debt';

        $this->hash = hash_hmac('sha256', $this->service_id . $this->client_rrn . $this->amount, $this->secret_key);

        $args = [
            'service_id' => $this->service_id,
            'uid' => $this->client_rrn,
            'client_rrn' => $this->client_rrn,
            'amount' => $this->amount,
            'client_ip' => $this->client_ip,
            'hash' => $this->hash,
        ];
        //$args['psp_rrn']='pass398wpd31'; // TEST

        $args_array = [];
        foreach ($args as $key => $value) {
            $args_array[] = '<input type="hidden" id="' . trim($key) . '" name="' . trim($key) . '" value="' . trim($value) . '" />';
        }

        return view('front.widgets.portmanat-debt', compact('action_local_adr', 'action_portmanat_adr', 'args_array', 'args','cd','track'));
    }

    public function generateFormKapital($cd = NULL,$track = NULL)
    {
        $action_local_adr = env('APP_URL') . '/portmanat/callback';//$this->mainUrl;
        //$action_local_adr = $this->mainUrl;// TEST
        $action_portmanat_adr = $this->mainUrl;

        if ($cd) {
            $action_local_adr = env('APP_URL') . '/portmanat/cd_callback';//$this->mainUrl;
            //$action_portmanat_adr = env('APP_URL').'/portmanat/cd_callback';//$this->mainUrl;
        }
        if ($track) {
            $action_local_adr = env('APP_URL') . '/portmanat/tr_callback';//$this->mainUrl;
            //$action_portmanat_adr = env('APP_URL').'/portmanat/tr_callback';//$this->mainUrl;
        }
        //$action_adr = $this->mainUrl;

        if($track) {
            $this->client_rrn = 'tr_'.$track->id.'_'.uniqid();
            $this->amount = $track->delivery_price_azn1;
        } else if($cd) {
            $this->client_rrn = 'cd_'.$cd->id.'_'.uniqid();
            $this->amount = $cd->delivery_price;
        } else {
            $this->client_rrn = uniqid();
            $this->amount = 0;
        }

        $this->hash = hash_hmac('sha256', $this->service_id . $this->client_rrn . $this->amount, $this->secret_key);

        $args = [
            'service_id' => $this->service_id,
            'uid' => $this->client_rrn,
            'client_rrn' => $this->client_rrn,
            'amount' => $this->amount,
            'client_ip' => $this->client_ip,
            'hash' => $this->hash,
        ];



        //$args['psp_rrn']='pass398wpd31'; // TEST

        $args_array = [];
        foreach ($args as $key => $value) {
            $args_array[] = '<input type="hidden" id="' . trim($key) . '" name="' . trim($key) . '" value="' . trim($value) . '" />';
        }

        return view('front.widgets.kapital', compact('action_local_adr', 'action_portmanat_adr', 'args_array', 'args','cd','track'));
    }

    public function generateFormNew($cd = NULL,$track = NULL)
    {
        $action_local_adr = env('APP_URL') . '/portmanat/callback';//$this->mainUrl;
        //$action_local_adr = $this->mainUrl;// TEST
        $action_portmanat_adr = env('APP_URL') .'/packages/portmanat/pay/package';
        if ($cd) {
            $action_local_adr = env('APP_URL') . '/portmanat/cd_callback';//$this->mainUrl;
            //$action_portmanat_adr = env('APP_URL').'/portmanat/cd_callback';//$this->mainUrl;
        }
        if ($track) {
            $action_local_adr = env('APP_URL') . '/portmanat/tr_callback';//$this->mainUrl;
            //$action_portmanat_adr = env('APP_URL').'/portmanat/tr_callback';//$this->mainUrl;
        }
        //$action_adr = $this->mainUrl;

        if($track) {
            $this->client_rrn = 'tr_'.$track->id.'_'.uniqid();
            $this->amount = $track->delivery_price_azn1;
        } else if($cd) {
            $this->client_rrn = 'cd_'.$cd->id.'_'.uniqid();
            $this->amount = $cd->delivery_price;
        } else {
            $this->client_rrn = uniqid();
            $this->amount = 0;
        }

        $this->hash = hash_hmac('sha256', $this->service_id . $this->client_rrn . $this->amount, $this->secret_key);

        $args = [
            'service_id' => $this->service_id,
            'uid' => $this->client_rrn,
            'client_rrn' => $this->client_rrn,
            'amount' => $this->amount,
            'client_ip' => $this->client_ip,
            'hash' => $this->hash,
        ];


        $botToken = "7784139238:AAGfstOZANbUgTV3hYKV8Xua8xQ_eJs5_wU";
        $website = "https://api.telegram.org/bot" . $botToken;
        $chatId = "-1002397303546";
        file_get_contents($website . "/sendMessage?chat_id=" . $chatId . "&text= ‼️ AseShop: ".$this->client_rrn.' - Az');

        //$args['psp_rrn']='pass398wpd31'; // TEST

        $args_array = [];
        foreach ($args as $key => $value) {
            $args_array[] = '<input type="hidden" id="' . trim($key) . '" name="' . trim($key) . '" value="' . trim($value) . '" />';
        }

        return view('front.widgets.portmanat-new', compact('action_local_adr', 'action_portmanat_adr', 'args_array', 'args','cd','track'));
    }

    public function generateUrlFromRequest(Request $request, $amount)
    {
        $action_adr = $this->mainUrl;
        $url = $this->mainUrl;
        $url .= '?service_id=' . $request->get('service_id');
        $url .= '&uid=' . $request->get('uid');
        $url .= '&client_rrn=' . $request->get('client_rrn');
        $url .= '&amount=' . $amount;
        $url .= '&client_ip=' . $request->get('client_ip');
        $url .= '&hash=' . $request->get('hash');
        $url .= '&psp_rrn=' . 'pass398wpd31';// test
        return $url;
        //return $this->mainUrl.'?'.$request->getQueryString();
    }
}
