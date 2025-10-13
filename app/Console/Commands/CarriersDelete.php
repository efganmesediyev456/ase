<?php

namespace App\Console\Commands;

use App\Models\CustomsModel;
use DB;
use Illuminate\Console\Command;

class CarriersDelete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carriers:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete package from carriers';

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
        $ldate = date('Y-m-d H:i:s');
        $cm = new CustomsModel();
        $this->info($ldate . "  ===== Started to delete carriers =====");
        $query = 'SELECT ';
        $query .= '  p.custom_id,pc.id,package_id as p_id,pc.fin,trackingNumber,pc.created_at,pc.is_commercial';
        //$query.=' FROM package_carriers pc';
        //$query.='  left outer join packages p on p.id=pc.package_id';
        //$query.="  p.id as p_id,pc.id as id,'XXX' as fin,p.custom_id as trackingNumber,p.created_at";
        $query .= ' FROM packages p';
        //$query.=' FROM package_carriers ';
        $query .= ' left outer join package_carriers pc on p.id=pc.package_id';
        //$query.=" where p.id=5217";
        $query .= " WHERE (code = 200 or code = 400)";
        $query .= " and (trackingNumber in ('ASE1234902118582'))";
        //$query.="  WHERE (code != 400) and (created_at > '2021-01-17 14:22:00')";
        //$query.=" where  p.user_id=10063 and p.created_at>'2022-05-17 00:00:00'";
        //$query.=' WHERE package_id in (141927,141942,142026)';
        //$query.=" where trackingNumber in ('ASE5869631979167')";
        //$query.=' limit 10';
        //$query.=" WHERE created_at>='2021-01-11 18:00:00'";
        //$query.=' and pc.id is null';
        //$query.=' and p.id in(46738,7020)';
        //$query.=' ORDER BY p.created_at';
        //$query.=' limit 1';
        //$this->info($query);
        //return;
        /*$query='SELECT';
        $query.=' pc.id,pc.package_id as p_id,pc.trackingNumber';
        $query.=' FROM package_carriers pc';
            $query.=' left outer join packages p on p.id=pc.package_id';
            $query.=' left outer join package_types pt on pt.id=p.type_id';
            $query.=' where pt.customs_good_id in';
        $query.='(39,40,41,43,44,49,50,47,51,67,69,76,71,75,68,72,70,74,77,90,486,486,486,486,487,487,487,488,486,487,487,486,486,487,487,489,194,187,193,450,490,453,453,453,453,453,453,453,453,453,453,453,453,453,453,453,453,453,453,453,453,453,453,453,453,193,453,453,453,453,453,453,453,453,453,180,193,453,453,453,453,453,453,453,453,453,193,453,453,453,453,453,441,441,441,441,441,441,441,453,453)';*/
        //echo $query;
        //return;
        $packages = DB::select($query);
        $cnt = 0;
        foreach ($packages as $package) {
            $cnt++;
            //$cm->trackingNumber = $package->trackingNumber;
            $cm->trackingNumber = $package->custom_id;
            $cm->isCommercial = false;//$package->is_commercial;
            echo $cnt . "  " . $package->trackingNumber . " Comm:" . $package->is_commercial . "\n";
            //continue;

            /*$tm=time();
            $ldate1 = Date('Y-m-d H:i:s',$tm-15*86400+15*60+1);
            $ldate2 = date('Y-m-d H:i:s',$tm+15*60);
            $cm->dateFrom = $ldate1;
            $cm->dateTo = $ldate2;
            $cm->pinNumber = $package->fin;
            $cm->trackingNumber = $package->trackingNumber;

            $res=$cm->carriersposts();
            if( ($res->code==200)
                  && !(isset($res->data) && is_array($res->data) && count($res->data)>0)
                      && !empty($package->pc_id) && !empty($package->created_at) )
             {
                  $tm=strtotime($package->created_at);
                  $ldate1 = Date('Y-m-d H:i:s',$tm-60*60);
                  $ldate2 = date('Y-m-d H:i:s',$tm+60*60);
                  $cm->dateFrom = $ldate1;
                  $cm->dateTo = $ldate2;
                  $cm->pinNumber = $package->fin;
                  $cm->trackingNumber = $package->trackingNumber;

                 $res=$cm->carriersposts();
            }

    $ldate = date('Y-m-d H:i:s');

            if(!isset($res->code))
            {
                    $this->info($ldate."  Error Package: ".$package->p_id." fin: ".$package->fin." trackNo: ".$package->trackingNumber);
                    $this->info("    Empty response ");
                    continue;
            }

            if($res->code != 200)
            {
                    $errorMessage='';
                    $validationError='';
                    if( isset($res->exception) && is_object($res->exception) )
                    {
                        $exception=$res->exception;
                        $errorMessage=$exception->errorMessage;
                        //print_r($exception);
                        $errs=[];
                        if(is_array($exception->validationError))
                             $errs=$exception->validationError;
                        if(is_object($exception->validationError))
                             $errs=get_object_vars($exception->validationError);
                        foreach($errs as $x => $x_value) {
                                if(!empty($validationError))
                                        $validationError.=" , ";
                                $validationError.=$x."=>".$x_value;
                        }
                        //$validationError=json_encode($exception->validationError);
                    }
                    $this->info($ldate."  Error Package: ".$package->p_id." fin: ".$package->fin." trackNo: ".$package->trackingNumber);
                    $this->info("    errorMessage: ".$errorMessage);
                    $this->info("    validationError: ".$validationError);
                    $this->info("  ----*******----- ");
                    print_r($res);
                    $this->info("  --------- ");
                    $this->info($cm->get_carriersposts_json_str());
                    $this->info("  ----*******----- ");
        continue;
            }

            if($res->code==200)
            {
                    if(isset($res->data) && is_array($res->data) && count($res->data)>0)
                    {
                        $cpost=$res->data[0];
            if(!empty($cpost->depesH_NUMBER) || !empty($cpost->ecoM_REGNUMBER))
            {
                            $this->info($ldate."  Warning Package: ".$package->p_id." fin: ".$package->fin." trackNo: ".$package->trackingNumber);
            if(!empty($cpost->depesH_NUMBER))
                                $this->info("    depeshNumber: ".$cpost->depesH_NUMBER);
            if(!empty($cpost->ecoM_REGNUMBER))
                                $this->info("    regNumber: ".$cpost->ecoM_REGNUMBER);
            continue;
            }
                    }
        else {
                $this->info($ldate."  Ok Package: ".$package->p_id." fin: ".$package->fin." trackNo: ".$package->trackingNumber." not exist deleted");
            DB::delete("delete from package_carriers where id=?",[$package->id]);
            continue;
        }
    }*/

            sleep(2);
            $res = $cm->delete_carriers();
            $ldate = date('Y-m-d H:i:s');

            if (!isset($res->code)) {
                $this->info($ldate . "  Error Package: " . $package->p_id . " fin: " . $package->fin . " trackNo: " . $package->trackingNumber);
                $this->info("    Empty response ");
                //DB::delete("delete from package_carriers where id=?",[$package->id]);
                continue;
            }
            //print_r($res);
            //continue;
            if ($res->code == 200) {
                /*if(isset($res->data) && is_array($res->data) && count($res->data)>0)
                {
                        $this->info($ldate."  Ok Package: ".$package->id." fin:".$package->fin." trackNo:".$package->custom_id." exists");
                    $this->info($ldate."     phone: ".$res->data[0]->phone);
                } else {
                        $this->info($ldate."  Ok Package: ".$package->id." fin:".$package->fin." trackNo:".$package->custom_id." not exists");
                }*/
                //DB::insert("insert into package_carriers (package_id,fin,trackingNumber,code,created_at) values (?,?,?,?,?)", [$package->id,$package->fin,$package->custom_id,$res->code,$ldate]);
                $this->info($ldate . "  Ok Package: " . $package->p_id . " fin: " . $package->fin . " trackNo: " . $package->trackingNumber . " exist deleted");
                if (!empty($package->id))
                    DB::delete("delete from package_carriers where id=?", [$package->id]);
            } else {
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
                $this->info($ldate . "  Error Package: " . $package->p_id . " fin: " . $package->fin . " trackNo: " . $package->trackingNumber);
                $this->info("    errorMessage: " . $errorMessage);
                //$this->info("    validationError: ".$validationError);
                //$this->info("  ----*******----- ");
                //print_r($res);
                //$this->info("  ----*******----- ");
                //DB::insert("insert into package_carriers (package_id,fin,trackingNumber,code,errorMessage,validationError,created_at) values (?,?,?,?,?,?,?)", [$package->id,$package->fin,$package->custom_id,$res->code,$errorMessage,$validationError,$ldate]);
                //DB::delete("delete from package_carriers where id=?",[$package->id]);
            }
        }
        //
    }
}
