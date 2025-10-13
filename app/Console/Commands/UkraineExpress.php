<?php

namespace App\Console\Commands;

use App\Models\Bag;
use App\Models\Extra\Notification;
use App\Models\Extra\SMS;
use App\Models\Package;
use App\Models\Parcel;
use App\Models\User as ModelUser;
use App\Models\Warehouse;
use DateTime;
use DB;
use Exception;
use Goutte\Client;
use Illuminate\Console\Command;
use Image;
use Symfony\Component\HttpClient\HttpClient;
use thiagoalessio\TesseractOCR\TesseractOCR;

class UkraineExpress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ukraine:express {--w_id=1} {--type=all} {--package_id=0} {--sync_parcel=0} {--sync_bag=0} {--sync_id=0}';

    protected $warehouse;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws Exception
     */
    public function handle()
    {
        try {
            $this->warehouse = Warehouse::find($this->option('w_id'));
            if ($this->option('type') == 'all') {
                $numbers = $this->getTrackingNumbers();
                //sleep(3);
                //$this->addNotAddedTrackingNumbers($numbers);
            } elseif ($this->option('type') == 'list') {
                $this->listTrack();
            } else if ($this->option('type') == 'notpacked') {
                $numbers = $this->getTrackingNumbers(true);
            } elseif ($this->option('type') == 'list') {
            } elseif ($this->option('type') == 'file') {
                $this->attachScreenFiles();
            } elseif ($this->option('type') == 'update') {
                $this->updateTracking();
            } elseif ($this->option('type') == 'rescan') {
                $this->rescanImage($this->option('package_id'));
            } elseif ($this->option('type') == 'sync') {
                if ($this->option('sync_id') == 0 && $this->option('sync_bag') == 0)
                    $this->sync_parcel($this->option('w_id'), $this->option('sync_parcel'));
                else
                    $this->sync($this->option('w_id'), $this->option('sync_id'), $this->option('sync_parcel'), $this->option('sync_bag'));
            } else {
                $this->runOCR();
            }
        } catch (Exception $exception) {
            dd($exception);
        }
    }

    public function sync_parcel($w_id, $sync_parcel)
    {
        if (empty($sync_parcel)) return;
        echo("===== Started to sync packages =====\n");
        echo("<br>\n");
        echo("   sync by parcel name: " . $sync_parcel . "\n");
        $client = $this->login();
        $ldate = date('Y-m-d H:i:s');
        $parcel = Parcel::where('custom_id', $sync_parcel)->where('warehouse_id', $w_id)->whereRaw("(TIME_TO_SEC(TIMEDIFF('" . $ldate . "',created_at))<=30*86400)")->first();
        if (!$parcel) {
            echo("Parcel not found\n");
            return;
        }
        echo("<table>\n");
        echo("<tr><th>package</th><th>tracking no</th><th>bag no</th></tr>\n");
        foreach ($parcel->packages as $package) {
            echo("<tr>");
            echo('    <td>' . $package->custom_id . '</td>' . "\n");
            echo('    <td>' . $package->tracking_code . '</td>' . "\n");
            $crawler = $client->request('GET', 'https://my.ukraine-express.com/index.php?m=clientarea&d=mytrackings&lang=ru&div=us&filter=' . $package->tracking_code);

            $weight = 0;


            //$desc = $crawler->filter('.invoice__description')->eq(0)->filter('.tracking__notices');
            $desc = $crawler->filter('.invoice__status');

            $countSubs = $desc->each(function ($node, $i) {
                return $i;
            });
            $countSubs = end($countSubs);

            /*if ($countSubs !== false) {
                for ($k = 0; $k <= $countSubs; $k++) {
                    if (str_contains($desc->eq($k)->text(), "Ориентированный вес")) {
                        $weight = str_replace("Ориентированный вес: ", "", $desc->eq($k)->text());
                        $weight = floatval(trim(str_replace("kg", "", $weight))) + 0;
                    }
                }
    }*/
            $sync_bag = '';

            if ($countSubs !== false) {
                for ($k = 0; $k <= $countSubs; $k++) {
                    if (str_contains($desc->eq($k)->text(), "Упаковано в посылку")) {
                        $sync_bag = trim(str_replace("Упаковано в посылку", "", $desc->eq($k)->text()));
                    }
                }
            }

            /*if($weight != 0 && $weight != $package->weight)
            {
                            $this->line("        updated weight " . $weight);
                //$package->status=0;
                //$package->weight=$weight;
                //$package->save();
            }*/
            if (empty($sync_bag)) {
                echo("    <td>Bag not found</td>\n");
                echo("</tr>\n");
                continue;
            }
            echo("    <td>" . $sync_bag . "</td>\n");
            //echo("</tr>\n"); continue;
            $bag = Bag::where('parcel_id', $parcel->id)->where('custom_id', $sync_bag)->first();
            if (!$bag) {
                $bag = new Bag();
                $bag->custom_id = $sync_bag;
                $bag->parcel_id = $parcel->id;
                $bag->save();
            }
            DB::delete("delete from bag_package where package_id=?", [$package->id]);
            DB::insert("insert into bag_package (bag_id,package_id) values (?,?)", [$bag->id, $package->id]);
            echo("</tr>\n");
        }
        echo("</table>\n");
    }

    public function sync($w_id, $sync_id, $sync_parcel, $sync_bag)
    {
        if (empty($sync_id)) return;
        if (empty($sync_parcel)) return;
        if (empty($sync_bag)) return;
        $this->line("");
        $this->info("===== Started to sync packages =====");
        $this->info("   sync id: " . $sync_id . "   parcel: " . $sync_parcel . "   bag: " . $sync_bag);
        $this->line("");
        $client = $this->login();
        $sync_ids = explode(',', $sync_id);
        $sync_bags = explode(',', $sync_bag);
        $tracks = [];
        $ldate = date('Y-m-d H:i:s');
        //$parcel = Parcel::where('custom_id', $sync_parcel)->where("sent",0)->where('warehouse_id', $w_id)->whereRaw("(TIME_TO_SEC(TIMEDIFF('".$ldate."',created_at))<=30*86400)")->first();

        $parcel = Parcel::where('custom_id', $sync_parcel)->where('warehouse_id', $w_id)->whereRaw("(TIME_TO_SEC(TIMEDIFF('" . $ldate . "',created_at))<=30*86400)")->first();
        if (!$parcel) {
            $parcel = new Parcel();
            $parcel->custom_id = $sync_parcel;
            $parcel->warehouse_id = $w_id;
            $parcel->save();
        }
        DB::delete("delete from parcel_package where parcel_id=?", [$parcel->id]);

        for ($j = 0; $j <= count($sync_ids) - 1; $j++) {
            $sync_id = $sync_ids[$j];
            $sync_bag = $sync_bags[$j];
            $crawler = $client->request('GET', 'https://my.ukraine-express.com/index.php?m=clientarea&d=parcelinfo&id=' . $sync_id . '&lang=ru&div=us');
            $tracks2 = $crawler->filter('td[title="Трекинг-номер"]')->each(function ($node, $i) {
                $tracs1 = [];
                $trackingNumber = $node->text();
                $code = $trackingNumber;
                $item = Package::withTrashed()->where("tracking_code", "like", $code . "%")->orderBy('deleted_at', 'asc')->first();
                $len = strlen($code) + 1;
                if (!$item && $len > 10) {
                    $item = Package::withTrashed()->whereRaw("length(tracking_code)>=8 and instr('" . $code . "',tracking_code)=greatest(" . $len . "-length(tracking_code),10)")->orderBy('deleted_at', 'asc')->first();
                }

                if (!$item && strlen($code) >= 10) {
                    $start = -1 * strlen($code) + 1;
                    $cnt = 0;
                    for ($i = $start; $i <= -8; $i++) {
                        $code = substr($code, $i);
                        $item = Package::withTrashed()->where("tracking_code", "like", $code . "%")->orderBy('deleted_at', 'asc')->first();
                        //$this->warn('Checking .. ' . $code);

                        if ($item) {
                            break;
                        }
                        $cnt++;
                        if ($cnt >= 8) break;
                    }
                }
                $tracks1[] = $item;//->custom_id;

                /*
                    if($item->parcel && count($item->parcel)>0)
                    $this->info($i."  ".$trackingNumber."   ".$item->custom_id."   ".$item->parcel[0]->custom_id."   ".$item->bag[0]->custom_id."  ");
                    else
                    $this->info($i."  ".$trackingNumber."   ".$item->custom_id."   ");
                 */
                return $tracks1;
            });
            //continue;
            //$this->info(end($count)+1);
            $bag = Bag::where('parcel_id', $parcel->id)->where('custom_id', $sync_bag)->first();
            if (!$bag) {
                $bag = new Bag();
                $bag->custom_id = $sync_bag;
                $bag->parcel_id = $parcel->id;
                $bag->save();
            }
            DB::delete("delete from bag_package where bag_id=?", [$bag->id]);
            foreach ($tracks2 as $track2) {
                $tracks[] = $track2[0];
                $package = $track2[0];//Package::whereCustomId($track2[0])->first();
                $this->line("package: " . $package->custom_id . "  parcel: " . $parcel->custom_id . "  bag:" . $bag->custom_id);
                DB::delete("delete from parcel_package where package_id=?", [$package->id]);
                DB::delete("delete from bag_package where package_id=?", [$package->id]);
                DB::insert("insert into parcel_package (parcel_id,package_id) values (?,?)", [$parcel->id, $package->id]);
                DB::insert("insert into bag_package (bag_id,package_id) values (?,?)", [$bag->id, $package->id]);
            }
        }
        //print_r($tracks);

        /*$str="select p.tracking_code,p.custom_id,pl.custom_id as pl_custom_id,b.custom_id as b_custom_id from packages p";
        $str.=" left outer join parcel_package pp on p.id=pp.package_id";
        $str.=" left outer join parcels pl on pp.parcel_id=pl.id";
        $str.=" left outer join bag_package bp on p.id=bp.package_id";
        $str.=" left outer join bags b on pp.parcel_id=b.id";
        $str.=" where pl.warehouse_id=".$w_id." and pl.custom_id='".$sync_parcel."' and p.custom_id not in ('".implode("','",$tracks)."')";
        //$this->info($str);
        $packages=DB::select($str);
        $cnt=0;
        foreach($packages as $package) {
            $cnt++;
            $this->info($cnt."   ".$package->tracking_code."   ".$package->custom_id."   ".$package->pl_custom_id."   ".$package->b_custom_id);
        }*/

    }

    public function addNotAddedTrackingNumbers($numbers)
    {
        $this->line("");
        $this->line("");
        $this->info("===== Started to add new packages =====");
        $this->line("");
        $this->line("");
        $warehouse = $this->warehouse;
        $packages = Package::where(function ($q) use ($warehouse) {
            $q->orWhere('warehouse_id', $warehouse->id)->orWhere('country_id', $warehouse->country_id);
        })->whereNotIn('tracking_code', $numbers)->whereNull('bot_comment')->where('status', 6)->get();
        //})->whereNotIn('tracking_code', $numbers)->where(function ($q) { $q->whereNull('bot_comment')->orWhere('bot_comment',"\n");})->where('status', 6)->get();
        dump(count($packages));

        foreach ($packages as $package) {
            dump($package->tracking_code);
            /*if($package && $package->user_id && $package->user && $package->user->ukr_express_id) {
              //if(!$item->ukr_express_id) {
                      file_put_contents('/var/log/ase_uexpress2_package_exluded.log',$ldate.' TO UKR_EXPRESS '.$package->id." ".$package->custom_id." ".$package->tracking_code." \n",FILE_APPEND);
              //}
              echo "Exluded ".$item->ukr_express_id."\n";
                  continue;
            }*/
            if (!in_array($package->tracking_code, $numbers) && $package->status == 6) {
                $code = $package->tracking_code;
                $exits = false;

                if (strlen($code) >= 10) {
                    $this->line("Checking  .. " . $code);
                    $start = -1 * strlen($code) + 1;
                    dump($start . " -- ");
                    for ($i = $start; $i <= -8; $i++) {
                        $code = substr($code, $i);
                        $this->line("Checking  .. " . $code);
                        if (in_array($code, $numbers)) {
                            $kk = array_search($code, $numbers);
                            $this->line($numbers[$kk]);
                            $this->error('Found');
                            $exits = true;
                            break 1;
                        }
                        foreach ($numbers as $number) {
                            if (str_contains($number, $code)) {
                                $exits = true;
                                break 2;
                            }
                        }
                    }
                }

                if (!$exits && strlen($package->tracking_code) >= 9) {
                    //$type = $package->type ? ($package->type->translate('en') ? $package->type->translate('en')->name : "Other") : 'Other';
                    $this->line("Adding  .. " . $package->tracking_code);
                    $type = $package->detailed_type;
                    if (empty($type)) $type = 'Other';

                    $warning = $this->addTracking($package->tracking_code, $package->shipping_amount, $type);

                    /* Send notification */
                    if ((!$warning || str_contains(trim($warning), 'внесли') || str_contains(trim($warning), 'добавлен') || str_contains(trim($warning), 'изменен')) && !str_contains(trim($warning), 'не может')) {
                        $info = false;
                        $icon = '🤖 ';
                        $text = 'əlavə edildi.';
                    } else {
                        $info = true;
                        $icon = '🆘 ';
                        $text = 'əlavə edilmir. Adminlər yoxlasın nə məsələdir.';
                        $content = $package->tracking_code . ". Error : " . $warning;
                        SMS::sendPureTextByNumber(env('UKRAINE_ERROR_PHONE'), $content);
                    }
                    $message = null;
                    $message .= $icon . "<a href='https://admin." . env('DOMAIN_NAME') . "/packages/" . $package->id . "/edit'>" . $package->tracking_code . "</a> tracking kod ilə olan bağlama Ukraine Express-ə " . $text;
                    if ($info) {
                        $message .= chr(10) . "Note : <i>" . $warning . "</i>";
                    }
                    sendTGMessage($message);

                    $package->bot_comment = $package->bot_comment . "\n" . $warning;
                    $package->save();
                }
            }
        }
    }

    public function addTracking($trackingCode, $price, $description)
    {
        if (strlen($trackingCode) < 9) {
            return false;
        }

        $client = $this->login();

        $crawler = $client->request('GET', 'https://my.ukraine-express.com/index.php?m=clientarea&d=dashboard&lang=ru&div=us');

        $this->info("Clicked to 'Добавить трекинг номер' button");

        $form = $crawler->filter('.deliveryorder_form')->form();

        $form->setValues(['tracking' => $trackingCode, 'price_t' => $price, 'description_t' => $description]);

        $crawler = $client->request($form->getMethod(), $form->getUri(), $form->getPhpValues());

        $this->line("Waiting");
        sleep(5);
        $warning = null;

        try {
            $warning = $crawler ? $crawler->filter('.alert-error')->eq(0) : null;
        } catch (Exception $exception) {
            $warning = $crawler ? $crawler->filter('.yellow_text')->eq(0) : null;
        }

        try {
            $warning = $warning ? $warning->text() : null;
        } catch (Exception $exception) {
            $warning = null;
        }

        $this->info("Added new tracking number : " . $trackingCode);

        return $warning;
    }

    public function login()
    {
        if ($this->warehouse->panel_login && $this->warehouse->panel_password) {
            $client = new Client(HttpClient::create(['timeout' => 60]));
            $crawler = $client->request('GET', 'https://ukraine-express.com/');
            $form = $crawler->filter('.form-login')->form();
            $client->submit($form, [
                'p_code' => $this->warehouse->panel_login,
                'p_pin' => $this->warehouse->panel_password,
            ]);

            $this->info("Logged to Ukraine Express panel.");
            sleep(2);

            return $client;
        } else {
            $this->error("No any credentials");
            exit();
        }
    }

    public function updateTracking()
    {
        $client = $this->login();

        $this->line("");
        $this->line("Started to update tracking ... ");
        $this->line("");
        $warehouse = $this->warehouse;
        $packages = Package::where(function ($q) use ($warehouse) {
            $q->orWhere('warehouse_id', $warehouse->id)->orWhere('country_id', $warehouse->country_id);
        })->whereRaw('ifnull(weight,0)=0')->where('status', 6)->whereNull('ukr_express_id')->get();
        foreach ($packages as $package) {
            $this->line(' -- package ' . $package->custom_id . '  tracking_code ' . $package->tracking_code);
            $crawler = $client->request('GET', 'https://my.ukraine-express.com/index.php?m=clientarea&d=mytrackings&lang=ru&div=us&filter=' . $package->tracking_code);

            $weight = 0;


            $desc = $crawler->filter('.invoice__description')->eq(0)->filter('.tracking__notices');

            $countSubs = $desc->each(function ($node, $i) {
                return $i;
            });
            $countSubs = end($countSubs);

            if ($countSubs !== false) {
                for ($k = 0; $k <= $countSubs; $k++) {
                    if (str_contains($desc->eq($k)->text(), "Ориентированный вес")) {
                        $weight = str_replace("Ориентированный вес: ", "", $desc->eq($k)->text());
                        $weight = floatval(trim(str_replace("kg", "", $weight))) + 0;
                    }
                }
            }

            if ($weight != 0) {
                $this->line("        updated weight " . $weight);
                $package->status = 0;
                $package->weight = $weight;
                $package->save();
            }
        }

    }

    public function listTrack($notpacked = true)
    {
        $client = $this->login();

        $this->line("");
        $notpackedStr = '';
        if ($notpacked) $notpackedStr = '&type=notpacked';
        $crawler = $client->request('GET', 'https://my.ukraine-express.com/index.php?m=clientarea&type=notpacked&d=mytrackings' . $notpackedStr . '&lang=ru&div=us&from=0');
        $pageBar = $crawler->filter('.pagebar a')->each(function ($node, $i) {
            return $i;
        });
        $pageBar = 0;
        $cnt1 = 0;
        $tracking_code = '';
        $weight = '';
        for ($m = 0; $m <= $pageBar; $m++) {

            //$this->line(' -- I just opened page : ' . $j);
            $crawler = $client->request('GET', 'https://my.ukraine-express.com/index.php?m=clientarea&d=mytrackings' . $notpackedStr . '&lang=ru&div=us&from=' . 30 * $m);

            $count = $crawler->filter('.trecking_number')->each(function ($node, $m) {
                return $m;
            });
            for ($j = 0; $j <= end($count); $j++) {
                $estr = '';

                $arr = [];

                $tracking_code = '';
                $desc = $crawler->filter('.invoice__description')->eq($j)->filter('.tracking__notices');

                $countSubs = $desc->each(function ($node, $j) {
                    return $j;
                });
                $countSubs = end($countSubs);

                if ($countSubs !== false) {
                    for ($k = 0; $k <= $countSubs; $k++) {
                        if (str_contains($desc->eq($k)->text(), "Полный номер")) {
                            $tracking_code = str_replace("Полный номер: ", "", $desc->eq($k)->text());
                        }
                        if (str_contains($desc->eq($k)->text(), "Ориентированный вес")) {
                            $weight = str_replace("Ориентированный вес: ", "", $desc->eq($k)->text());
                            $weight = floatval(trim(str_replace("kg", "", $weight))) + 0;
                        }
                        $estr .= "---" . $desc->eq($k)->text();
                    }
                }
                $desc = $crawler->filter('.invoice__status')->eq($j);

                $countSubs = $desc->each(function ($node, $j) {
                    return $j;
                });
                $countSubs = end($countSubs);

                if ($countSubs !== false) {
                    for ($k = 0; $k <= $countSubs; $k++) {
                        $estr .= "---" . $desc->eq($k)->text();
                    }
                }
                if (empty($tracking_code))
                    $tracking_code = $crawler->filter('.trecking_number')->eq($j)->text();

                //if(!empty($weight)) continue;
                $cnt1++;
                $code = $tracking_code;
                //$this->error($code);
                $item = Package::withTrashed()->where("tracking_code", "like", $code . "%")->orderBy('deleted_at', 'asc')->first();
                $len = strlen($code) + 1;
                if (!$item && $len > 10) {
                    $item = Package::withTrashed()->whereRaw("length(tracking_code)>=8 and instr('" . $code . "',tracking_code)=greatest(" . $len . "-length(tracking_code),10)")->orderBy('deleted_at', 'asc')->first();
                }

                if (!$item && strlen($code) >= 10) {
                    $start = -1 * strlen($code) + 1;
                    $cnt = 0;
                    for ($i = $start; $i <= -8; $i++) {
                        $code = substr($code, $i);
                        $item = Package::withTrashed()->where("tracking_code", "like", $code . "%")->orderBy('deleted_at', 'asc')->first();

                        if ($item) {
                            break;
                        }
                        $cnt++;
                        if ($cnt >= 8) break;
                    }
                }

                if ($item /*&& (!$item->parcel || !$item->parcel->count()<=0)*/) {
                    //echo($cnt1." ". $tracking_code." ".$weight);
                    if (!$item->user)
                        echo $tracking_code . "---NO---" . $item->custom_id . $estr . "\n";
                    else
                        echo $tracking_code . "---" . $item->user->customer_id . "---" . $item->custom_id . $estr . "\n";
                } else if (!$item) {
                    //echo($cnt1." ". $tracking_code." ".$weight);
                    echo $tracking_code . "---NO---NO" . $estr . "\n";
                }

            }
        }
    }


    public function getTrackingNumbers($notpacked = false)
    {
        $client = $this->login();

        $this->line("");
        if ($notpacked)
            $this->line("Started to get Tracking numbers notpacked ... ");
        else
            $this->line("Started to get Tracking numbers ... ");
        $notpackedStr = '';
        if ($notpacked) $notpackedStr = '&type=notpacked';
        $crawler = $client->request('GET', 'https://my.ukraine-express.com/index.php?m=clientarea&d=mytrackings' . $notpackedStr . '&lang=ru&div=us&from=0');
        $pageBar = $crawler->filter('.pagebar a')->each(function ($node, $i) {
            return $i;
        });
        $pageBar = 260;//end($pageBar) + 1;
        //$pageBar = 600;//end($pageBar) + 1;
        if ($notpacked) {
            $pageBar = 0;
        }

        $trackingNumbers = [];
        $dnow = time();

        for ($j = 0; $j <= $pageBar; $j++) {

            $this->line(' -- I just opened page : ' . $j);
            $crawler = $client->request('GET', 'https://my.ukraine-express.com/index.php?m=clientarea&d=mytrackings' . $notpackedStr . '&lang=ru&div=us&from=' . 30 * $j);

            $count = $crawler->filter('.trecking_number')->each(function ($node, $i) {
                return $i;
            });

            for ($i = 0; $i <= end($count); $i++) {

                $arr = [];
                $arr['forbitten'] = NULL;
                //$arr['transport']=NULL;
                $arr['weight'] = 0;
                $arr['photo'] = NULL;
                $arr['date'] = NULL;
                $arr['days'] = NULL;
                /*try {
                            $arr['transport'] = $crawler->filter('.transport-ico')->eq($i)->attr('title');
                } catch (\Exception $e) {
                }*/
                try {
                    $date = substr(str_replace('Получено на склад ', '', $crawler->filter('.trecking_code__logitems')->eq($i)->text()), 0, 8);
                    $d = DateTime::createFromFormat("d.m.y", $date);
                    $arr['date'] = $date;
                    $arr['days'] = round(0 + ($dnow - $d->getTimestamp()) / 86400, 0);
                    //echo $date." -- ".$d->format('Y-m-d H:i:s')."  diff: ".round(0+($dnow-$d->getTimestamp())/86400,0)."\n";
                } catch (Exception $e) {
                    echo $e->getMessage() . "\n";
                }

                try {
                    $arr['trackNumber'] = $crawler->filter('.trecking_number')->eq($i)->text();
                } catch (Exception $e) {
                    break 2;
                }
                $desc = $crawler->filter('.invoice__description')->eq($i)->filter('.tracking__notices');

                $countSubs = $desc->each(function ($node, $i) {
                    return $i;
                });
                $countSubs = end($countSubs);

                if ($countSubs !== false) {
                    for ($k = 0; $k <= $countSubs; $k++) {
                        if (str_contains($desc->eq($k)->text(), "Полный номер")) {
                            $arr['trackNumber'] = str_replace("Полный номер: ", "", $desc->eq($k)->text());
                        }
                        if (str_contains($desc->eq($k)->text(), "Ориентированный вес")) {
                            $arr['weight'] = str_replace("Ориентированный вес: ", "", $desc->eq($k)->text());
                            $arr['weight'] = floatval(trim(str_replace("kg", "", $arr['weight']))) + 0;
                        }
                        if (str_contains($desc->eq($k)->text(), "Запрещенный груз")) {
                            $arr['forbitten'] = $desc->eq($k)->text();
                        }
                        if (str_contains($desc->eq($k)->text(), "автоматического разрешения")) {
                            $arr['forbitten'] = $desc->eq($k)->text();
                        }
                        if (str_contains($desc->eq($k)->text(), "Фото при прийоме")) {
                            $arr['photo'] = $desc->eq($k)->text();
                        }
                        //if (str_contains($desc->eq($k)->text(), "Получено")) {
                        //    $arr['got'] = $desc->eq($k)->text();
                        //}
                        //echo $desc->eq($k)->text()."\n";
                    }
                }
                //if(!$arr['forbitten'] && !$arr['transport'] && $arr['weight'] && $arr['photo']) {
                if (!$arr['forbitten'] && /*$arr['weight'] && $arr['photo'] && */ $arr['days'] && $arr['days'] >= 1 && $arr['days'] <= 40) {
                    //$this->line(" ---- Getting Ok " . $arr['trackNumber'].'  '.$arr['forbitten'].'  '.$arr['transport'].'  '.$arr['weight'].' '.$arr['photo'].' date:'.$arr['date']);
                    $this->line(" ---- Getting Ok " . $arr['trackNumber'] . '  ' . $arr['forbitten'] . '  ' . $arr['weight'] . ' ' . ' ' . $arr['date']);
                    array_push($trackingNumbers, $arr);
                } else {
                    //$this->line(" ---- Getting " . $arr['trackNumber'].'  '.$arr['forbitten'].'  '.$arr['transport'].'  '.$arr['weight'].'  '.$arr['photo'].' date:'.$arr['date']);
                    $this->line(" ---- Getting " . $arr['trackNumber'] . '  ' . $arr['forbitten'] . '  ' . $arr['weight'] . '  ' . ' ' . $arr['date'] . "  days:" . $arr['days']);
                }
            }
        }
        //return;

        $this->line("");
        $this->line("");
        $this->line("---------------------------------");
        $this->line("---- Started to check with DB ---");
        $this->line("---------------------------------");

        $numbers = [];
        foreach ($trackingNumbers as $list) {
            $ldate = date('Y-m-d H:i:s');
            $code = $list['trackNumber'];
            $this->error($code);
            $item = Package::withTrashed()->where("tracking_code", "like", $code . "%")->orderBy('deleted_at', 'asc')->first();
            $len = strlen($code) + 1;
            if (!$item && $len > 10) {
                $item = Package::withTrashed()->whereRaw("length(tracking_code)>=8 and instr('" . $code . "',tracking_code)=greatest(" . $len . "-length(tracking_code),10)")->orderBy('deleted_at', 'asc')->first();
            }

            if (!$item && strlen($code) >= 10) {
                $start = -1 * strlen($code) + 1;
                $cnt = 0;
                for ($i = $start; $i <= -8; $i++) {
                    $code = substr($code, $i);
                    $item = Package::withTrashed()->where("tracking_code", "like", $code . "%")->orderBy('deleted_at', 'asc')->first();
                    //$this->warn('Checking .. ' . $code);

                    if ($item) {
                        break;
                    }
                    $cnt++;
                    if ($cnt >= 8) break;
                }
            }

            $numbers[] = $list['trackNumber'];
            if (isset($list['weight'])) {

                if (!$item) {
                    $this->error(" ---- Cannot find " . $list['trackNumber'] . ". Inserting ... ");
                    $screenFile = $this->getImage($client, $list['trackNumber']);
                    //echo "image: ".$screenFile."\n";

                    $package = new Package();
                    $package->tracking_code = $list['trackNumber'];
                    if (!$package->weight) {
                        $this->warn(" --- > " . $list['weight'] . "weight was updated");
                        $package->weight = $list['weight'];
                    }

                    $package->warehouse_id = $this->warehouse->id;
                    $package->status = 0;
                    $package->screen_file = $screenFile;
                    $package->bot_comment = 'notpacked';
                    $package->save();

                    /* Send notification */
                    $message = null;
                    $message .= "🆘 <a href='https://admin." . env('DOMAIN_NAME') . "/unknowns?search_type=&q=" . $package->tracking_code . "'>" . $package->tracking_code . "</a> bazamızda tapılmadı və UE-dan migrate edildi. Sahibi mütləq tapılmalıdır.";

                    if ($screenFile) {
                        $message .= " Çəkilmiş şəklin linki : " . $screenFile;
                    }

                    sendTGMessage($message);
                } else {
                    if (!$item->weight) {
                        $this->warn(" --- > " . $list['weight'] . "weight was updated");
                        $item->weight = $list['weight'];
                    }
                    $item->warehouse_id = $this->warehouse->id;
                    if ($item->user_id && !$item->show_label) {
                        $item->show_label = true;
                    }
                    if ($item->status == 6) {
                        /*if($item && $item->user_id && $item->user && $item->user->ukr_express_id) {
                    if(!$item->ukr_express_id) {
                                file_put_contents('/var/log/ase_uexpress2_package_exluded.log',$ldate.' FROM UKR_EXPRESS '.$item->id." ".$item->custom_id." ".$item->tracking_code." \n",FILE_APPEND);
                    }
                        echo "Exluded ".$item->ukr_express_id."\n";
                            continue;
                }*/
                        // Send Notification
                        Notification::sendPackage($item->id, '0');
                        /* Send notification */
                        $message = null;
                        $message .= "🌀️ <a href='https://admin." . env('DOMAIN_NAME') . "/packages/" . $item->id . "/edit'>" . $list['trackNumber'] . '(' . $item->tracking_code . ")</a> tracking kod ilə olan bağlamanın çəkisi " . $list['weight'] . "kq olaraq yeniləndi və sahibinə (" . ($item->user ? $item->user->full_name : 'Tapılmadı') . ") notification göndərildi.";

                        $item->u_tracing_code = $item->tracking_code;
                        $item->tracking_code = $list['trackNumber'];
                        $item->status = 0;
                        $item->bot_comment = 'notpacked';
                        $item->save();
                        sendTGMessage($message);
                    }

                    if (!$item->screen_file && !$item->user_id) {
                        $screenFile = $this->getImage($client, $list['trackNumber']);
                        if ($screenFile) {
                            $this->info(" --- Added image for " . $list['trackNumber']);
                            $item->screen_file = $screenFile;

                            /* Send notification */
                            $message = null;
                            $message .= "📸 <a href='https://admin." . env('DOMAIN_NAME') . "/unknowns?search_type=&q=" . $item->tracking_code . "'>" . $item->tracking_code . "</a> tracking kod ilə olan bağlamamın yiyəsi tapılması üçün şəkil əlavə edildi.";
                            $message .= " Çəkilmiş şəklin linki : " . $screenFile;

                            sendTGMessage($message);
                        } else {
                            $this->warn("No image for : " . $list['trackNumber']);
                        }
                    }
                    $item->bot_comment = 'notpacked';

                    $item->save();
                }
            }
        }

        return $numbers;
    }

    public function rescanImage($id)
    {
        if (empty($id)) {
            $this->info("Empty package id ");
            return;
        }
        $package = Package::find($id);
        if (!$package) {
            $this->info("Package not exists " . $id);
            return;
        }
        if ($package->warehouse_id != 11) {
            $this->info("Package is not in Ukraine Express Warehouse");
            return;
        }
        $client = $this->login();
        $new_screen_file = $this->getImage($client, $package->tracking_code);
        if (!$new_screen_file) {
            $this->info("Cannot get Image file from Ukraine Express website");
            //    return;
        } else {
            if (!empty($package->screen_file)) {
                $filePath = str_replace('https://aseshop.az/', '', $package->screen_file);
                if (file_exists(public_path($filePath)))
                    unlink(public_path($filePath));
            }
            $package->screen_file = $new_screen_file;
        }
        //$package->save();

        $screen_file = $package->screen_file;
        if (!$screen_file) {
            $this->info("No screen file " . $package->id);
            return;
        }
        $ocr = $this->ocrRequest($screen_file);
        if ($ocr) {
            /* Send notification */
            $message = null;
            $message .= "✅ <a href='https://admin." . env('DOMAIN_NAME') . "/packages/" . $package->id . "/edit'>" . $package->tracking_code . "</a> tracking kod ilə olan yiyəsiz bağlamanın sahibini <b>OCR</b> ilə tapdım. Yenə də manual yoxlanmalıdır.";
            $message .= chr(10) . "Original şəkil : " . $package->screen_file;
            $message .= chr(10) . "Tapılmış Customer ID : <b>" . $ocr['customer_id'] . "</b>";
            if ($ocr['user']) {
                $this->info('OCR: Found the user : ' . $ocr['customer_id']);
                $message .= chr(10) . "Tapılmış Müştəri : <b>" . $ocr['user']->full_name . "</b>";
                $package->user_id = $ocr['user']->id;
                $package->save();
            } else {
                $message .= chr(10) . "Curtomer bazada tapılmadı";
                $this->info('OCR: User not found');
                $package->admin_comment = 'User not found';
                $package->save();
            }
            sendTGMessage($message);
            exit();
        } else {
            $package->admin_comment = 'Bot (OCR) cannot read the owner. Original image : ' . $package->screen_file;
            $package->save();
            $this->error('OCR: cannot read the owner');
        }
    }


    public function getImage($client, $trackingNumber)
    {
        try {
            $crawler = $client->request('GET', 'https://my.ukraine-express.com/index.php?m=clientarea&d=trackingphotos&tracking=' . $trackingNumber . '&lang=ru&div=us#labelphotos');
            $path = $crawler->filter('.track-photo-thumb-label a')->eq(0)->attr('href');
            if (!$path) {
                return null;
            }

            $filename = "front_" . basename($path);

            Image::make($path)->save(public_path('uploads/packages/' . $filename));

            return asset('uploads/packages/' . $filename);
        } catch (Exception $exception) {
            return null;
        }
    }

    public function runOCR()
    {
        $packages = Package::where('warehouse_id', $this->warehouse->id)->whereNull('user_id')->where('id', '>', 3000)->whereNull('admin_comment')->whereNotNull('screen_file')->get();

        foreach ($packages as $package) {
            $this->info("Started : " . $package->screen_file);
            $ocr = $this->ocrRequest($package->screen_file);
            if ($ocr) {
                /* Send notification */
                $message = null;
                $message .= "✅ <a href='https://admin." . env('DOMAIN_NAME') . "/packages/" . $package->id . "/edit'>" . $package->tracking_code . "</a> tracking kod ilə olan yiyəsiz bağlamanın sahibini <b>OCR</b> ilə tapdım. Yenə də manual yoxlanmalıdır.";
                $message .= chr(10) . "Original şəkil : " . $package->screen_file;
                $message .= chr(10) . "Tapılmış Customer ID : <b>" . $ocr['customer_id'] . "</b>";
                if ($ocr['user']) {
                    $message .= chr(10) . "Tapılmış Müştəri : <b>" . $ocr['user']->full_name . "</b>";
                    //$message .= chr(10) . "Telefon : " . $ocr['user']->phone;
                    $package->user_id = $ocr['user']->id;
                    $package->save();
                } else {
                    $message .= chr(10) . "Curtomer bazada tapılmadı";
                    $package->admin_comment = 'User not found';
                    $package->save();
                }
                $this->info('Found the user : ' . $ocr['customer_id']);
                sendTGMessage($message);
                exit();
            } else {
                $package->admin_comment = 'Bot (OCR) cannot read the owner. Original image : ' . $package->screen_file;
                $package->save();
                $this->error('Cannot find');
            }
        }
    }

    public function attachScreenFiles()
    {
        $packages = Package::where('warehouse_id', $this->warehouse->id)->whereNull('user_id')->where('id', '>', 3000)->whereNull('screen_file')->get();
        $client = $this->login();
        foreach ($packages as $package) {
            $screenFile = $this->getImage($client, $package->tracking_code);
            $package->screen_file = $screenFile;
            $package->save();

            if ($screenFile) {
                /* Send notification */
                $message = null;
                $message .= "🤖 <a href='https://admin." . env('DOMAIN_NAME') . "/packages/" . $package->id . "/edit'>" . $package->tracking_code . "</a> sahibi yoxdur, tapılması üçün screen file əlavə edildi. Çəkilmiş şəklin linki : " . $screenFile;

                //dump($message);
                sendTGMessage($message);
            }
        }
    }

    public function ocrRequest($url)
    {

        for ($i = 2; $i >= 1; $i--) {
            if ($i == 1) sleep(1);
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://api8.ocr.space/parse/image?trackingId=423d4645484542433b3c42423c4241464141443d3b46&carrier=Auto-Detect&language=en&country=Russian%2BFederation&platform=web-desktop&timestamp=1579848456645&wd=false&c=false&p=2&l=2",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30000,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "-----011000010111000001101001\r\nContent-Disposition: form-data; name=\"url\"\r\n\r\n" . $url . "\r\n-----011000010111000001101001\r\nContent-Disposition: form-data; name=\"OCREngine\"\r\n\r\n" . $i . "\r\n-----011000010111000001101001\r\nContent-Disposition: form-data; name=\"scale\"\r\n\r\ntrue\r\n-----011000010111000001101001\r\nContent-Disposition: form-data; name=\"isSearchablePdfHideTextLayer\"\r\n\r\ntrue\r\n-----011000010111000001101001\r\nContent-Disposition: form-data; name=\"FileType\"\r\n\r\n.jpg\r\n-----011000010111000001101001--\r\n",
                CURLOPT_HTTPHEADER => [
                    "apikey: 5a64d478-9c89-43d8-88e3-c65de9999580",
                    "content-type: multipart/form-data; boundary=---011000010111000001101001",
                    "host: api8.ocr.space",
                    "origin: http://ocr.space",
                    "referer: http://ocr.space/",
                    "sec-fetch-site: cross-site",
                ],
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                $this->error('Error. Cannot read the image!');
            } else {
                $data = \GuzzleHttp\json_decode($response, true);

                if (isset($data['ParsedResults'][0]['ParsedText'])) {
                    $text = $data['ParsedResults'][0]['ParsedText'];
                    $cId = $this->findASECustomerId($text);

                    //dd($cId);
                    return $cId;
                } else {
                    $this->error('Error. Cannot read the image! OCR issue');
                }
            }
        }

        return false;
    }

    public function findASECustomerId($text)
    {
        preg_match('/ASE[0-9]{4,5}/', $text, $result_array);
        if ($result_array && isset($result_array[0]) && !empty($result_array[0])) {
            $cId = $result_array[0];
            $user = ModelUser::where('customer_id', $cId)->first();

            return [
                'customer_id' => $cId,
                'user' => $user ? $user : null,
            ];
        } else {
            preg_match('/ASE [0-9]{4,5}/', $text, $result_array);

            if ($result_array && isset($result_array[0]) && !empty($result_array[0])) {
                $cId = str_replace(" ", "", $result_array[0]);
                $user = ModelUser::where('customer_id', $cId)->first();

                return [
                    'customer_id' => $cId,
                    'user' => $user ? $user : null,
                ];
            }
        }

        return false;
    }
}
