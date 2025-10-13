<?php

namespace App\Console\Commands;

use App\Models\CustomsModel;
use App\Models\Package;
use App\Models\Track;
use App\Services\Package\PackageService;
use DB;
use Illuminate\Console\Command;

class Depesh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'depesh {package} {parcel_id} {checkonly=1} {htmlformat=0} {user_id=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Depesh all packages in parcel';

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
     */
    public function handle()
    {
        set_time_limit(3 * 3600);
        $lastUpdateTime = 0;
        $checkOnly = $this->argument("checkonly");
        $packageOnly = $this->argument("package");
        $htmlFormat = $this->argument("htmlformat");
        $id = $this->argument('parcel_id');
        $user_id = $this->argument('user_id');
        //echo "$id $checkOnly $htmlFormat\n";
        //return;
        $depeshes = [];
        $ldate = date('Y-m-d H:i:s');
        $cm = new CustomsModel();
        $cm_ae = new CustomsModel(true);
        $cm->retryCount = 7;
        $cm->retrySleep = 0;
        $cm_ae->retryCount = 7;
        $cm_ae->retrySleep = 0;
        if (!$checkOnly) {
            if ($htmlFormat) {
                echo "<h2>Depesh packages</h2><br>\n";
            } else {
                $this->info($ldate . "  ===== Started to depesh packages =====");
            }
        }

        $query = 'SELECT ';
        $query .= ' pl.id as pl_id,pl.custom_id as pl_custom_id,b.custom_id as b_custom_id';
        $query .= ' ,p.id,p.custom_id as custom_id,pc.trackingNumber';
        $query .= ' ,t.id as t_id,t.tracking_code as t_tracking_coder,t.fin as t_fi,t.fin as t_fin';
        $query .= ',pc.trackingNumber';
        $query .= ' ,pc.fin as pc_fin,pc.id as pc_id,pc.created_at as pc_created_at,u.fin,pc.check_customs as pc_check_customs,pc.is_commercial as pc_is_commercial';
        $query .= ' ,pc.inserT_DATE,pc.ecoM_REGNUMBER,pc.depesH_DATE,pc.depesH_NUMBER,pc.status,pc.airwaybill,pc.insert_date_dec';
        $query .= ' ,pc.depesH_NUMBER_1,pc.airwaybill_1';
        $query .= '   ,case when (p.status = 0';
        $query .= '   and p.number_items is not null and p.number_items > 0 ';
        $query .= '   and p.shipping_amount is not null and p.shipping_amount > 0 ';
        $query .= '   and p.weight is not null and p.weight > 0 ';
        $query .= '   and p.deleted_at is null) then true else false end as is_ready ';
        $query .= ' FROM  package_carriers pc';
        $query .= ' left outer join packages p on pc.package_id=p.id';
        $query .= ' left outer join tracks t on pc.track_id=t.id';
        $query .= ' LEFT OUTER JOIN parcel_package pp on pp.package_id=p.id';
        $query .= ' LEFT OUTER JOIN parcels pl on pp.parcel_id=pl.id';
        $query .= ' LEFT OUTER JOIN bag_package bp on bp.package_id=p.id';
        $query .= ' LEFT OUTER JOIN bags b on bp.bag_id=b.id';
        $query .= ' LEFT OUTER JOIN users u on p.user_id=u.id';
        $query .= ' WHERE p.deleted_at is null and t.deleted_at is null';
        $queryOne = $query;
        if ($packageOnly == 1)
            $query .= ' and p.id=' . $id;
        else if ($packageOnly == 0)
            $query .= ' and pl.id=' . $id;
        else if ($packageOnly == 2)
            $query .= ' and b.id=' . $id;
        else if ($packageOnly == 4)
            $query .= ' and pc.id=' . $id;
        else if ($packageOnly == 5)
            $query .= ' and t.id=' . $id;
        else if ($packageOnly == 6)
            $query .= ' and t.container_id=' . $id;
        else if ($packageOnly == 7)
            $query .= ' and t.airbox_id=' . $id;
        else if ($packageOnly == 3) { //manual query
            $query .= " and p.custom_id in ('ASE4004975281704','ASE8226256180582')";
        }
        //$query .= ' ORDER BY p.created_at';
        $query .= ' order by pp.parcel_id,t.airbox_id,p.created_at,t.created_at';

        //$this->info($query);
        $packages = DB::select($query);
        if ($htmlFormat) {
            if ($checkOnly) {
                if ($packageOnly == 1)
                    echo "<h3>Check PACKAGE carriers post & declarations</h3><br>\n";
                else if ($packageOnly == 0)
                    echo "<h3>Check PARCEL carriers post & declarations</h3><br>\n";
                else if ($packageOnly == 2)
                    echo "<h3>Check BAG carriers post & declarations</h3><br>\n";
            } else {
                if ($packageOnly == 1)
                    echo "<h3>PACKAGE carriers post & declarations</h3><br>\n";
                else if ($packageOnly == 0)
                    echo "<h3>PARCEL carriers post & declarations</h3><br>\n";
                else if ($packageOnly == 2)
                    echo "<h3>BAG carriers post & declarations</h3><br>\n";
            }

            echo "<table class='tdep'>\n";
            echo "<tr>\n";
            echo "<th>No</th>\n";
            echo "<th>Time</th>\n";
            echo "<th>ID</th>\n";
            echo "<th>FIN</th>\n";
            echo "<th>Commercial</th>\n";
            echo "<th>Tracking Nunmber</th>\n";
            echo "<th>In Customs</th>\n";
            echo "<th>Status</th>\n";
            echo "<th>Weight</th>\n";
            echo "<th>Invoice Price</th>\n";
            echo "<th>Invoice Price<br>Smart Declaration</th>\n";
            echo "<th>Declaration</th>\n";
            echo "<th>Airwaybill</th>\n";
            echo "<th>Depesh</th>\n";
            echo "</tr>\n";
        } else {
            //$this->info($query);
            $this->info($ldate . "  ===== Checking packages in carrier posts =====");
        }
        $pn = 0;
        foreach ($packages as $package) {
            $pn++;

            if (!empty($package->pc_id)) {
                $ones = DB::select($queryOne . " and pc.id=" . $package->pc_id);
                if (count($ones) <= 0) {
                    $this->info($ldate . "    Error: DB changed ");
                    $package = null;
                    if ($htmlFormat) {
                        echo "<tr>\n";
                        echo "<td>" . $pn . "</td>\n";
                        echo "<td>" . $ldate . "</td>\n";
                        echo "<td colspan=7>DB Error (package updated)</td>\n";
                    }
                    continue;
                }
                $package = $ones[0];
            }

            $depesh = ['inserT_DATE' => '', 'airwaybill' => '', 'depesH_NUMBER' => '', 'depesH_DATE' => '', 'status' => '', 'ecoM_REGNUMBER' => ''];
            $depesh['trackingNumber'] = $package->trackingNumber;
            $depesh['package_id'] = $package->id;
            $depesh['track_id'] = $package->t_id;
            $depesh['track'] = NULL;
            if (!empty($package->pc_fin))
                $depesh['fin'] = $package->pc_fin;
            else
                $depesh['fin'] = $package->fin;

            $date1 = $package->inserT_DATE;
            if (empty($date1) || ($date1 == '0000-00-00 00:00:00')) {
                $date1 = $package->pc_created_at;
            }
            if (!empty($package->pc_fin)) {
                $cm->pinNumber = $package->pc_fin;
                $cm_ae->pinNumber = $package->pc_fin;
            } else {
                $cm->pinNumber = $package->fin;
                $cm_ae->pinNumber = $package->t_fin;
            }
            $cm->isCommercial = $package->pc_is_commercial;
            $cm->trackingNumber = $package->trackingNumber;
            $cm_ae->isCommercial = false;
            $cm_ae->trackingNumber = $package->trackingNumber;

            if ($htmlFormat) {
                echo "<tr>\n";
                echo "<td>" . $pn . "</td>\n";
                echo "<td>" . $ldate . "</td>\n";
                if ($package->id)
                    echo "<td>" . $package->id . "</td>\n";
                else
                    echo "<td>" . $package->t_id . "</td>\n";
            }

            $insertDate = $package->inserT_DATE;
            $regNumber = $package->ecoM_REGNUMBER;
            $depeshDate = $package->depesH_DATE;
            $depeshNumber = $package->depesH_NUMBER;
            $airwaybill = $package->airwaybill;
            $regDate = $package->insert_date_dec;


            if (!empty($package->pc_id) && !$package->pc_check_customs) {
                if ($htmlFormat) {
                    echo "<td><font color=blue colspan=6>Package's user is not using smart customs</font></td>\n";
                    echo "</tr>\n";
                } else {
                    $this->error($ldate . "  Error Package: " . $package->id . "  fin: " . $package->fin . "  trackNo: " . $package->custom_id . "  User is not using smart customs");
                }
                //$this->info($ldate."  ===== One of packages has no declaration =====");
                continue;
            }

            /*if(!empty($insertDate) && !empty($regNumber) && !empty($depeshNumber) && !empty($depeshDate))
            {
                 if($htmlFormat)
                 {
                      echo "<td><font color=green>".$insertDate."</font></td>\n";
                      echo "<td><font color=green>".$regNumber."</font></td>\n";
                      echo "<td><font color=green>".$depeshDate." ( ".$depeshNumber." )</font></td>\n";
                      echo "</tr>\n";
                 }
             else {
                 $this->error($ldate."  Error Package: ".$package->id."  fin: ".$package->fin."  trackNo: ".$package->custom_id."  already depesh");
             }
                 //$this->info($ldate."  ===== One of packages has no declaration =====");
             continue;
            }*/
            $cpost = NULL;
            $_package = NULL;
            $_track = NULL;
            if ($package->id) {
                if ($checkOnly) {
                    $cpost = $cm->get_carrierposts2();
                }
                $_package = Package::find($package->id);
            } else if ($package->t_id) {
                if ($checkOnly) {
                    $cpost = $cm_ae->get_carrierposts2();
                }
                $_track = Track::find($package->t_id);
            }
            $weight = 0;

            if (!empty($package->pc_id)) {
                $ones = DB::select($queryOne . " and pc.id=" . $package->pc_id);
                if (count($ones) <= 0) {
                    $this->info($ldate . "    Error: DB changed ");
                    $package = null;
                    if ($htmlFormat) {
                        if ($package->id) {
                            echo "<td>" . $cm->pinNumber . "</td>\n";
                            if ($cm->isCommercial)
                                echo "<td><font color=blue>YES</font></td>\n";
                            else
                                echo "<td>NO</td>\n";
                        } else {
                            echo "<td>" . $cm_ae->pinNumber . "</td>\n";
                            if ($cm_ae->isCommercial)
                                echo "<td><font color=blue>YES</font></td>\n";
                            else
                                echo "<td>NO</td>\n";
                        }
                        echo "<td>" . $package->trackingNumber . "</td>\n";
                        echo "<tr>\n";
                        echo "<td>" . $pn . "</td>\n";
                        echo "<td>" . $ldate . "</td>\n";
                        echo "<td colspan=6>DB Error (package updated)</td>\n";
                    }
                    continue;
                }
                $package = $ones[0];
            }

            $ldate = date('Y-m-d H:i:s');


            if (!$checkOnly || $cpost->code == 999) {
                $insertDate = $package->inserT_DATE;
                $regNumber = $package->ecoM_REGNUMBER;
                $depeshDate = $package->depesH_DATE;
                $depeshNumber = $package->depesH_NUMBER;
                $airwaybill = $package->airwaybill;
                $status = $package->status;
                $regDate = $package->insert_date_dec;
                if (!empty($regNumber)) {
                    //if (!empty($insertDate)) {
                    $depesh['inserT_DATE'] = $insertDate;
                    $depesh['depesH_NUMBER'] = $depeshNumber;
                    $depesh['depesH_DATE'] = $depeshDate;
                    $depesh['ecoM_REGNUMBER'] = $regNumber;
                    $depesh['airwaybill'] = $airwaybill;
                    $depesh['insertDateDeclaration'] = $regDate;
                    $depesh['status'] = $status;
                    $statusStr = $status;
                    switch ($status) {
                        case 1:
                            $statusStr .= " (has declaration)";
                            break;
                        case 2:
                            $statusStr .= " (has addtobox)";
                            break;
                        case 3:
                            $statusStr .= " (has depesh)";
                            break;
                        case 70:
                            $statusStr .= "  (Bağlamanın aid olduğu aviaqaimə Gömrük anbarına daxil olub. Dəyişiklik etmək olmaz.)";
                            break;
                    }
                    if ($htmlFormat) {
                        if ($package->id) {
                            echo "<td>" . $cm->pinNumber . "</td>\n";
                            if ($cm->isCommercial)
                                echo "<td><font color=blue>YES</font></td>\n";
                            else
                                echo "<td>NO</td>\n";
                        } else {
                            echo "<td>" . $cm_ae->pinNumber . "</td>\n";
                            if ($cm_ae->isCommercial)
                                echo "<td><font color=blue>YES</font></td>\n";
                            else
                                echo "<td>NO</td>\n";
                        }
                        echo "<td>" . $package->trackingNumber . "</td>\n";
                        if (!$checkOnly)
                            echo "<td> " . $insertDate . "</td>\n";
                        else
                            echo "<td><font color=red>Err: Empty response</font> " . $insertDate . "</td>\n";
                        echo "<td>" . $statusStr . "</td>\n";
                        if ($_package) {
                            echo "<td>" . $_package->getWeight() . "/td>";
                            echo "<td>" . $_package->shipping_org_price . "</td>";
                            echo "<td>" . $_package->carriers_cost_with_label . "</td>";
                        } else {
                            echo "<td>" . $_track->weight . "</td>";
                            echo "<td>" . $_track->shipping_amount_with_label . "</td>";
                            echo "<td>" . $_track->carriers_cost_with_label . "</td>";
                        }
                    } else {
                        $this->error($ldate . "  Error Package: " . $package->id . "  fin: " . $package->fin . "  trackNo: " . $package->custom_id);
                        $this->error("    Empty response continue with insertDate=" . $insertDate);
                        //echo $cm->get_carriersposts_json_str();
                    }
                } else {
                    if ($htmlFormat) {
                        if (!$checkOnly) {
                            echo "<td colspan=6><font color=red>No Registration Number</font></td>\n";
                            echo "</tr>\n";
                        } else {
                            echo "<td colspan=6><font color=red>Err: Empty response </font></td>\n";
                            echo "</tr>\n";
                        }
                    } else {
                        $this->error($ldate . "  Error Package: " . $package->id . "  fin: " . $package->fin . "  trackNo: " . $package->custom_id);
                        $this->error("    Empty response ");
                        //echo $cm->get_carriersposts_json_str();
                    }
                    continue;
                }
            } else if ($cpost->code != 200) {
                if ($htmlFormat) {
                    if ($package->id) {
                        echo "<td>" . $cm->pinNumber . "</td>\n";
                        if ($cm->isCommercial)
                            echo "<td><font color=blue>YES</font></td>\n";
                        else
                            echo "<td>NO</td>\n";
                    } else {
                        echo "<td>" . $cm_ae->pinNumber . "</td>\n";
                        if ($cm_ae->isCommercial)
                            echo "<td><font color=blue>YES</font></td>\n";
                        else
                            echo "<td>NO</td>\n";
                    }
                    echo "<td>" . $package->trackingNumber . "</td>\n";
                    echo "<td colspan=7><font color=red>Err: " . $cpost->errorMessage . " " . $cpost->validationError . "</font></td>\n";
                    echo "</tr>\n";
                } else {
                    $this->error("    errorMessage: " . $cm->errorMessage);
                    $this->error("    validationError: " . $cm->validationError);
                    $this->error("  ----*******----- ");
                    print_r($res);
                    $this->error("  --------- ");
                    $this->error($cm->get_carriersposts_json_str());
                    $this->error("  ----*******----- ");
                    /*if(!empty($package->pc_id))
                    {
                        DB::update("update package_carriers set code=?,errorMessage=?,validationError=? where id=?"
                            , [$res->code,$cm->errorMessage,$cm->validationError,$package->pc_id]);
                    } else {
                      DB::insert("insert into package_carriers (package_id,fin,trackingNumber,code,errorMessage,validationError,created_at) values (?,?,?,?,?,?,?)"
                            , [$package->id,$package->fin,$package->custom_id,$res->code,$cm->errorMessage,$cm->validationError,$ldate]);
                    }*/
                }
                continue;
            } else if ($cpost->code == 200) {
                if (!empty($cpost->inserT_DATE)) {

                    $depesh['inserT_DATE'] = $cpost->inserT_DATE;
                    $depesh['airwaybill'] = $cpost->airwaybill;
                    $depesh['depesH_NUMBER'] = $cpost->depesH_NUMBER;
                    $depesh['depesH_DATE'] = $cpost->depesH_DATE;
                    $depesh['airwaybill'] = $cpost->airwaybill;
                    if (empty($cpost->ecoM_REGNUMBER)) {
                        //$depesh['status']=$package->status;
                        $depesh['ecoM_REGNUMBER'] = $package->ecoM_REGNUMBER;
                    } else {
                        $depesh['ecoM_REGNUMBER'] = $cpost->ecoM_REGNUMBER;
                    }
                    $depesh['status'] = $cpost->status;
                    $depesh['insertDateDeclaration'] = $regDate;
                    $weight = $cpost->weighT_GOODS;
                    $status = $depesh['status'];
                    $statusStr = $status;
                    switch ($status) {
                        case 1:
                            $statusStr .= " (has declaration)";
                            break;
                        case 2:
                            $statusStr .= " (has addtobox)";
                            break;
                        case 3:
                            $statusStr .= " (has depesh)";
                            break;
                        case 70:
                            $statusStr .= "  (Bağlamanın aid olduğu aviaqaimə Gömrük anbarına daxil olub. Dəyişiklik etmək olmaz.)";
                            break;
                    }

                    if (!$htmlFormat) {
                        /*$this->info("  --------- ");
                        $this->info($cm->get_carriersposts_json_str());
                        $this->info("  ----*******----- ");
                        print_r($res);
        $this->info("  ----*******----- ");
         */
                    }

                    /*if(empty($cpost->inserT_DATE))
                            $cpost->inserT_DATE=NULL;
                    if(empty($cpost->airwaybill))
                            $cpost->airwaybill=NULL;
                    if(empty($cpost->depesH_NUMBER))
                            $cpost->depesH_NUMBER=NULL;
                    if(empty($cpost->depesH_DATE))
                            $cpost->depesH_DATE=NULL;
                    if(empty($cpost->status))
                            $cpost->status=NULL;
                    if(empty($cpost->ecoM_REGNUMBER))
            $cpost->ecoM_REGNUMBER=NULL;*/

                    if ($htmlFormat) {
                        echo "<td>" . $cpost->fin . "</td>\n";
                        if ($package->id) {
                            if ($cm->isCommercial)
                                echo "<td><font color=blue>YES</font></td>\n";
                            else
                                echo "<td>NO</td>\n";
                        } else {
                            if ($cm_ae->isCommercial)
                                echo "<td><font color=blue>YES</font></td>\n";
                            else
                                echo "<td>NO</td>\n";
                        }
                        echo "<td>" . $package->trackingNumber . "</td>\n";
                        echo "<td><font color=green>" . $cpost->inserT_DATE . "</font></td>";
                        echo "<td>" . $statusStr . "</td>\n";
                        if (($_package && abs($cpost->weighT_GOODS - $_package->getWeight()) > 0) || ($_track && abs($cpost->weighT_GOODS - $_track->weight) > 0)) {
                            //$cm->weighT_GOODS=$_package->getWeight();
                            //$cm->weight_carriers();
                            //
                            if ($_package)
                                echo "<td><font color=red>" . $cpost->weighT_GOODS . " != " . $_package->getWeight() . "</font></td>";
                            else
                                echo "<td><font color=red>" . $cpost->weighT_GOODS . " != " . $_track->weight . "</font></td>";
                        } else {
                            echo "<td><font color=green>" . $cpost->weighT_GOODS . "</font></td>";
                        }
                        if ($_track) {
                            echo "<td>" . $_track->shipping_amount_with_label . "</td>";
                            echo "<td>" . $_track->carriers_cost_with_label . "</td>";
                        } else {
                            echo "<td>" . $_package->shipping_org_price . "</td>";
                            echo "<td>" . $_package->carriers_cost_with_label . "</td>";
                        }
                    } else {
                        $this->info($ldate . "  Ok Package: " . $package->id . "  fin: " . $package->fin . "  trackNo: " . $package->custom_id);
                    }
                    $cm->updateDB($package->id, $package->fin, $package->custom_id, $ldate, $cpost);

                } else {
                    if ($htmlFormat) {
                        if ($package->id) {
                            echo "<td>" . $cm->pinNumber . "</td>\n";
                            if ($cm->isCommercial)
                                echo "<td><font color=blue>YES</font></td>\n";
                            else
                                echo "<td>NO</td>\n";
                        } else {
                            echo "<td>" . $cm_ae->pinNumber . "</td>\n";
                            if ($cm_ae->isCommercial)
                                echo "<td><font color=blue>YES</font></td>\n";
                            else
                                echo "<td>NO</td>\n";
                        }
                        echo "<td>" . $package->trackingNumber . "</td>\n";
                        echo "<td><font color=red>No</font></td><td colspan=5></td>\n";
                        echo "</tr>\n";
                    } else {
                        $this->info($ldate . "  Ok Package: " . $package->id . "  fin: " . $package->fin . "  trackNo: " . $package->custom_id . "  not exists");
                    }
                    $cm->deleteDBTrack($package->id);
                    //$this->info($ldate."  ===== One of packages is not in carriers posts =====");
                    continue;
                }
            }

            //-------------------------

            $regNumber = $depesh['ecoM_REGNUMBER'];
            $regDate = $depesh['insertDateDeclaration'];
            $depeshDate = $depesh['depesH_DATE'];
            $depeshNumber = $depesh['depesH_NUMBER'];
            $airwaybill = $depesh['airwaybill'];
            $status = $depesh['status'];

            if (empty($regNumber) && empty($depeshNumber)) {
                if ($htmlFormat) {
                    //     echo "<td><font color=red>No</font></td><td></td><td>/td>\n";
                    //     echo "</tr>\n";
                } else {
                    $this->error($ldate . "  Error Package: " . $package->id . "  fin: " . $package->fin . "  trackNo: " . $package->custom_id . "  no declaration");
                }
                //$this->info($ldate."  ===== One of packages has no declaration =====");
                if (!empty($status) && $status >= 3) {
                    echo "<td><font color=red>No</font></td><td></td><td></td>";
                    echo "</tr>\n";
                    continue;
                }
            }
            if ($htmlFormat) {
                if (!empty($regDate))
                    echo "<td><font color=green>" . $regDate . " ( " . $regNumber . " )</font></td>";
                else if (!empty($regNumber))
                    echo "<td><font color=green>" . $regNumber . "</font></td>";
                else
                    echo "<td><font color=red>No</font></td>";
            }

            //  if($cm->isCommercial) {

            $cm->trackingNumber = $package->trackingNumber;
            if ($package->id) {
                $cm->airwaybill = $package->custom_id;
                $cm->depesH_NUMBER = $package->custom_id;
                if ($package->b_custom_id && $package->pl_custom_id) {
                    $cm->depesH_NUMBER = $package->b_custom_id;
                    $cm->airwaybill = $package->pl_custom_id;
                }
            } else if ($package->t_id && $_track) {
                $cm->airwaybill = $_track->tracking_code;
                $cm->depesH_NUMBER = $_track->tracking_code;
                $cm_ae->airwaybill = $_track->tracking_code;
                $cm_ae->depesH_NUMBER = $_track->tracking_code;
                if ($_track->container && $_track->container->name) {
                    $cm->airwaybill = $_track->container->name;
                    $cm_ae->airwaybill = $_track->container->name;
                }
                if ($_track->airbox && $_track->airbox->name) {
                    $cm->depesH_NUMBER = $_track->airbox->name;
                    $cm_ae->depesH_NUMBER = $_track->airbox->name;
                }

            } else {
                $cm->airwaybill = $package->airwaybill_1;
                $cm->depesH_NUMBER = $package->depesH_NUMBER_1;
            }

            //$dweight=$_package->getWeight();
            //if(($depeshNumber != $cm->depesH_NUMBER) || ($airwaybill != $cm->airwaybill) || ($dweight != $weight)) {
            if (((!empty($depeshNumber) || $cm->isCommercial) && ($depeshNumber != $cm->depesH_NUMBER))
                || ((!empty($airwaybill) || $cm->isCommercial) && ($airwaybill != $cm->airwaybill))) {
                //$cm->weighT_GOODS=$dweight;

                if ($cm->isCommercial) {
                    if ($lastUpdateTime > 0 && $lastUpdateTime < 30) {
                        sleep(30 - $lastUpdateTime);
                    }
                    $lastUpdateTime = time();
                }

                if ($package->id) {
                    $res = $cm->update_carriers();
                } else {
                    $res = $cm_ae->update_carriers();
                }
                if (!isset($res->code)) {
                    echo "<td><font color=red>" . $airwaybill . " change error: Empty response</front></td>";
                    echo "<td><font color=red>" . $depeshDate . " ( " . $depeshNumber . " ) change error: Empty response</font></td>";
                } else if ($res->code != 200) {
                    if ($package->id) {
                        echo "<td><font color=red>" . $airwaybill . " change error: " . $cm->errorStr . "</front></td>";
                        echo "<td><font color=red>" . $depeshDate . " ( " . $depeshNumber . " ) change error: " . $cm->errorStr . "</font></td>";
                    } else {
                        echo "<td><font color=red>" . $airwaybill . " change error: " . $cm_ae->errorStr . "</front></td>";
                        echo "<td><font color=red>" . $depeshDate . " ( " . $depeshNumber . " ) change error: " . $cm_ae->errorStr . "</font></td>";
                    }
                } else {
                    $str = "update package_carriers";
                    $str .= " set depesH_NUMBER=?,airwaybill=?";
                    $str .= " where id=?";

                    DB::update($str, [
                        $cm->depesH_NUMBER, $cm->airwaybill
                        , $package->pc_id]);

                    if ($package->id) {
                        echo "<td><font color=blue>" . $airwaybill . " changed to: " . $cm->airwaybill . "</front></td>";
                        echo "<td><font color=blue>" . $depeshDate . " ( " . $depeshNumber . " changed to: " . $cm->depesH_NUMBER . " )</font></td>";
                    } else {
                        echo "<td><font color=blue>" . $airwaybill . " changed to: " . $cm_ae->airwaybill . "</front></td>";
                        echo "<td><font color=blue>" . $depeshDate . " ( " . $depeshNumber . " changed to: " . $cm_ae->depesH_NUMBER . " )</font></td>";
                    }
                }
            } else {
                if (!empty($airwaybill)) {
                    echo "<td><font color=green>" . $airwaybill . "</front></td>";
                } else {
                    echo "<td><font color=red>No</front></td>";
                }
                if (!empty($depeshNumber)) {
                    echo "<td><font color=green>" . $depeshDate . " ( " . $depeshNumber . " )</font></td>";
                } else {
                    echo "<td><font color=red>No</front></td>";
                }
            }
            echo "</tr>\n";
            if ($package->id && $cm->isCommercial) {
                continue;
            }
            /*if($package->t_id && $_track && in_array($_track->status,[14,16,17])) {
                    continue;
            }*/
            //}

            /*if(!empty($airwaybill))
            {
                 if($htmlFormat)
                 {
                      echo "<td><font color=green>".$airwaybill."</front></td>";
                 }
            }
            else {
                 if($htmlFormat)
                 {
                      echo "<td><font color=red>No</font></td>";
                 }
            }

            if(!empty($depeshNumber))
            {
                 if($htmlFormat)
                 {
                      echo "<td><font color=green>".$depeshDate." ( ".$depeshNumber." )</font></td>";
                 }
             else {
                 $this->error($ldate."  Error Package: ".$package->id."  fin: ".$package->fin."  trackNo: ".$package->custom_id."  already depesh ".$depeshDate." ( ".$depeshNumber." )");
             }
             if(!empty($status) && $status>=3) {
                     echo "</tr>\n";
                 continue;
             }
            } else {
                if($htmlFormat)
                {
                    echo "<td><font color=red>No</font></td>\n";
               }
            }
            if($htmlFormat)
            {
                echo "</tr>\n";
            } */

            if ($package->id) {
                $depesh['airWaybill'] = $package->pl_custom_id;
                if ($package->b_custom_id) {
                    $depesh['depeshNumber'] = $package->b_custom_id;
                } else {
                    $depesh['depeshNumber'] = $package->pl_custom_id;
                }
            } else {
                $depesh['airWaybill'] = $_track->tracking_code;
                $depesh['depeshNumber'] = $_track->tracking_code;
                $depesh['track'] = $_track;
                if ($_track->container && $_track->container->name)
                    $depesh['airWaybill'] = $_track->container->name;
                if ($_track->airbox && $_track->airbox->name)
                    $depesh['depeshNumber'] = $_track->airbox->name;
            }

            $depeshes[] = $depesh;
        }

        if ($htmlFormat) {
            echo "</table><br>\n";
        } else {
            $this->info($ldate . "  ===== END CHECK =====");
        }

        if ($checkOnly) {
            return;
        }

        if ($htmlFormat) {
            echo "<h3> " . count($depeshes) . " packages to depesh</h3><br>\n";
        } else {
            $this->info($ldate . "  ===== " . count($depeshes) . " packages to depesh =====");
        }

        if (count($depeshes) <= 0) {
            return;
        }

        if ($htmlFormat) {
            echo "<br><br>\n";
            echo "<h3>Depesh packages</h3><br>\n";
            echo "<table class='tdep'>\n";
            echo "<tr>\n";
            echo "<th>No</th>\n";
            echo "<th>Time</th>\n";
            echo "<th>ID</th>\n";
            echo "<th>FIN</th>\n";
            echo "<th>Tracking Number</th>\n";
            echo "<th>approvesearch</th>\n";
            echo "<th>addtoboxes</th>\n";
            echo "<th>depesh</th>\n";
            echo "</tr>\n";
        } else {
            $this->info($ldate . "  ===== Depesh packages =====");
        }

        $pn = 0;
        foreach ($depeshes as $depesh) {

            $pn++;

            $ldate = date('Y-m-d H:i:s');
            if ($htmlFormat) {
                echo "<tr>\n";
                echo "<td>" . $pn . "</td>\n";
                echo "<td>" . $ldate . "</td>\n";
                if ($package->id)
                    echo "<td>" . $depesh['package_id'] . "</td>\n";
                else
                    echo "<td>" . $depesh['track_id'] . "</td>\n";
                echo "<td>" . $depesh['fin'] . "</td>\n";
                echo "<td>" . $depesh['trackingNumber'] . "</td>\n";
            } else {
                $this->info($ldate . "  Package: " . $depesh['package_id'] . "  fin: " . $depesh['fin'] . "  trackNo: " . $depesh['trackingNumber']);
                $this->info("    approvesearch");
            }

            //approvesearch
            $status = $depesh['status'];
            $_track = $depesh['track'];
            $res = NULL;
            if ($package->id) {
                $cm->regNumber = $depesh['ecoM_REGNUMBER'];
                $res = $cm->approvesearch();
            } else {
                $cm_ae->regNumber = $depesh['ecoM_REGNUMBER'];
                $res = $cm_ae->approvesearch();
            }
            if ($status >= 2) {
                if ($htmlFormat) {
                    echo "<td><font color=green>Ok status=" . $status . " (has addtoboxes)</font></td>\n";
                } else {
                    $this->info("     Ok");
                }
            } else {
                if ($packageOnly && !$htmlFormat) {
                    echo "Approve search request:\n";
                    if ($package->id)
                        echo $cm->get_approvesearch_request();
                    else
                        echo $cm_ae->get_approvesearch_request();
                    echo "Approve search result:\n";
                    //print_r($res);
                    echo(json_encode($res, JSON_PRETTY_PRINT));
                    echo "\n";
                }
                if (!isset($res->code)) {
                    if ($htmlFormat) {
                        echo "<td colspan=3><font color=red>Err: Empty response</font></td>\n";
                        echo "</tr>\n";
                    } else {
                        $this->error("   Error: empty response ");
                    }
                    continue;
                }

                if ($res->code != 200) {
                    $errorMessage = '';
                    $validationError = '';
                    if (isset($res->exception) && is_object($res->exception)) {
                        $exception = $res->exception;
                        $errorMessage = $exception->errorMessage;
                        //print_r($exception);
                        $errs = [];
                        if (is_array($exception->validationError))
                            $errs = $exception->validationError;
                        if (is_object($exception->validationError))
                            $errs = get_object_vars($exception->validationError);
                        foreach ($errs as $x => $x_value) {
                            if (!empty($validationError))
                                $validationError .= " , ";
                            $validationError .= $x . "=>" . $x_value;
                        }
                        //$validationError=json_encode($exception->validationError);
                    }
                    if ($htmlFormat) {
                        echo "<td colspan=3><font color=red>Err: " . $errorMessage . " " . $validationError . "</font></td>\n";
                        echo "</tr>\n";
                    } else {
                        $this->error("    errorMessage: " . $errorMessage);
                        $this->error("    validationError: " . $validationError);
                    }
                    continue;
                }

                if ($htmlFormat) {
                    echo "<td><font color=green>Ok</font></td>\n";
                } else {
                    $this->info("     Ok");
                }
            }
            //---------

            //addtoboxes
            if (!$htmlFormat)
                $this->info("     addtoboxes");
            if ($status >= 2) {
                if ($htmlFormat) {
                    echo "<td><font color=green>Ok status=" . $status . " (has addtoboxes)</font></td>\n";
                } else {
                    $this->info("     Ok");
                }
            } else {

                $res = NULL;
                if ($package->id) {
                    $cm->trackingNumber = $depesh['trackingNumber'];
                    $res = $cm->addtoboxes();
                } else {
                    $cm_ae->trackingNumber = $depesh['trackingNumber'];
                    $res = $cm_ae->addtoboxes();
                }
                if ($packageOnly && !$htmlFormat) {
                    echo "Addtoboxes request:\n";
                    if ($package->id)
                        echo $cm->get_addtoboxes_request();
                    else
                        echo $cm_ae->get_addtoboxes_request();
                    echo "Addtoboxes result:\n";
                    //print_r($res);
                    echo(json_encode($res, JSON_PRETTY_PRINT));
                    echo "\n";
                }
                if (!isset($res->code)) {
                    if ($htmlFormat) {
                        echo "<td colspan=2><font color=red>Err: Empty response</font></td>\n";
                        echo "</tr>\n";
                    } else {
                        $this->error("   Error: empty response ");
                    }
                    continue;
                }

                if ($res->code != 200) {
                    $errorMessage = '';
                    $validationError = '';
                    if (isset($res->exception) && is_object($res->exception)) {
                        $exception = $res->exception;
                        $errorMessage = $exception->errorMessage;
                        //print_r($exception);
                        $errs = [];
                        if (is_array($exception->validationError))
                            $errs = $exception->validationError;
                        if (is_object($exception->validationError))
                            $errs = get_object_vars($exception->validationError);
                        foreach ($errs as $x => $x_value) {
                            if (!empty($validationError))
                                $validationError .= " , ";
                            $validationError .= $x . "=>" . $x_value;
                        }
                        //$validationError=json_encode($exception->validationError);
                    }
                    if ($htmlFormat) {
                        echo "<td><font color=red>Err: " . $errorMessage . " " . $validationError . " (" . $res->code . ")</font></td>\n";
                        //            echo "</tr>\n";
                    } else {
                        $this->error("    errorMessage: " . $errorMessage);
                        $this->error("    validationError: " . $validationError);
                    }
                    // continue;
                } else {
                    if ($htmlFormat) {
                        echo "<td><font color=green>Ok</font></td>\n";
                    } else {
                        $this->info("     Ok");
                    }
                }
            }
            //---------

            //depesh
            if (!$htmlFormat)
                $this->info("    depesh");
            $cm->airWaybill = $depesh['airWaybill'];
            $cm->depeshNumber = $depesh['depeshNumber'];
            $cm_ae->airWaybill = $depesh['airWaybill'];
            $cm_ae->depeshNumber = $depesh['depeshNumber'];
            if ($status >= 3) {
                //if($_track && !in_array($_track->status,[14,16,17])) {
                if ($_track && in_array($_track->status, [1, 5, 7])) {
                    $_track->status = 14;
                    $_track->save();
                    (new PackageService())->updateStatus($_track, 14);
                }
                if ($htmlFormat) {
                    echo "<td><font color=green>Ok status=" . $status . " (has depesh)</font></td>\n";
                    echo "</tr>\n";
                } else {
                    $this->info("     Ok");
                }
            } else {
                if ($package->id)
                    $res = $cm->depesh();
                else
                    $res = $cm_ae->depesh();
                if ($packageOnly && !$htmlFormat) {
                    echo "Depesh request:\n";
                    if ($package->id)
                        echo $cm->get_depesh_request();
                    else
                        echo $cm_ae->get_depesh_request();
                    echo "Depesh result:\n";
                    //print_r($res);
                    echo(json_encode($res, JSON_PRETTY_PRINT));
                    echo "\n";
                }
                if (!isset($res->code)) {
                    if ($htmlFormat) {
                        echo "<td><font color=red>Err: Empty response</font></td>\n";
                        echo "</tr>\n";
                    } else {
                        $this->error("   Error: empty response ");
                    }
                    continue;
                }

                if ($res->code != 200) {
                    $errorMessage = '';
                    $validationError = '';
                    if (isset($res->exception) && is_object($res->exception)) {
                        $exception = $res->exception;
                        $errorMessage = $exception->errorMessage;
                        //print_r($exception);
                        $errs = [];
                        if (is_array($exception->validationError))
                            $errs = $exception->validationError;
                        if (is_object($exception->validationError))
                            $errs = get_object_vars($exception->validationError);
                        foreach ($errs as $x => $x_value) {
                            if (!empty($validationError))
                                $validationError .= " , ";
                            $validationError .= $x . "=>" . $x_value;
                        }
                        //$validationError=json_encode($exception->validationError);
                    }
                    if ($htmlFormat) {
                        echo "<td><font color=red>Err: " . $errorMessage . " " . $validationError . "</font></td>\n";
                        echo "</tr>\n";
                    } else {
                        $this->error("    errorMessage: " . $errorMessage);
                        $this->error("    validationError: " . $validationError);
                    }
                    continue;
                }
                //if($_track && !in_array($_track->status,[14,16,17])) {
                if ($_track && in_array($_track->status, [1, 5, 7])) {
                    $_track->status = 14;
                    $_track->save();
                    (new PackageService())->updateStatus($_track, 14);
                }

                if ($htmlFormat) {
                    $ldate = date('Y-m-d H:i:s');
                    if ($package->id) {
                        $str = "update package_carriers";
                        $str .= " set depesH_NUMBER=?,depesH_DATE=?";
                        $str .= " where package_id=?";

                        DB::update($str, [
                            $depesh['depeshNumber'], $ldate
                            , $depesh['package_id']]);
                    } else {
                        $str = "update package_carriers";
                        $str .= " set depesH_NUMBER=?,depesH_DATE=?";
                        $str .= " where track_id=?";

                        DB::update($str, [
                            $depesh['depeshNumber'], $ldate
                            , $depesh['track_id']]);
                    }

                    //$str = "insert into logs_depesh(package,user_id,package_id,created_at) values(?,?,?,?)";
                    //DB::insert($str, [$packageOnly, $user_id, $depesh['package_id'], $ldate]);

                    echo "<td><font color=green>Ok</font></td>\n";
                    echo "</tr>\n";
                } else {
                    $this->info("     Ok");
                }
            }
            //---------
        }

        if ($htmlFormat) {
            echo "</table>\n";
        } else {
            $this->info($ldate . "  ===== END DEPESH =====");
        }
    }
}
