<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Models\Package;
use App\Models\Warehouse;
use Goutte\Client;
use Symfony\Component\HttpClient\HttpClient;

class TestController extends \App\Http\Controllers\Controller
{



    public function login()
    {
        $warehouse = Warehouse::find(11);
        if ($warehouse->panel_login && $warehouse->panel_password) {
            $client = new Client(HttpClient::create(['timeout' => 60]));
            $crawler = $client->request('GET', 'https://ukraine-express.com/');
            $form = $crawler->filter('.form-login')->form();
            $client->submit($form, [
                'p_code' => $warehouse->panel_login,
                'p_pin' => $warehouse->panel_password,
            ]);

            $this->info("Logged to Ukraine Express panel.");
            sleep(2);

            return $client;
        } else {
            $this->error("No any credentials");
            exit();
        }
    }
    public function index(){
        $client = $this->login();

        $this->line("");
        $this->line("Started to update tracking ... ");
        $this->line("");
        $tracking_code = 'TBA322389602799';
        $this->line('  tracking_code ' . $tracking_code);
        $crawler = $client->request('GET', 'https://my.ukraine-express.com/index.php?m=clientarea&d=mytrackings&lang=ru&div=us&filter=' . $tracking_code);
        return $crawler;
//        $weight = 0;
//
//
//        $desc = $crawler->filter('.invoice__description')->eq(0)->filter('.tracking__notices');
//
//        $countSubs = $desc->each(function ($node, $i) {
//            return $i;
//        });
//        $countSubs = end($countSubs);
//
//        if ($countSubs !== false) {
//            for ($k = 0; $k <= $countSubs; $k++) {
//                if (str_contains($desc->eq($k)->text(), "Ориентированный вес")) {
//                    $weight = str_replace("Ориентированный вес: ", "", $desc->eq($k)->text());
//                    $weight = floatval(trim(str_replace("kg", "", $weight))) + 0;
//                }
//            }
//        }

    }
}