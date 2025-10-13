<?php

namespace App\Traits;

use App\Models\Activity;
use Auth;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;

/**
 * Class ModelEventLogger
 *
 * @package App\Traits
 *
 *  Automatically Log Add, Update, Delete events of Model.
 */
trait ModelEventLogger
{
    /**
     * Automatically boot with Model, and register Events handler.
     */
    protected static function bootModelEventLogger()
    {
        foreach (static::getRecordActivityEvents() as $eventName) {
            static::$eventName(function (Model $model) use ($eventName) {
                try {
                    $reflect = new ReflectionClass($model);
                    if (Auth::guard('admin')->check()) {

                        if (static::getActionName($eventName) == 'delete') {
                            /* Send notification */
                            $message = null;
                            $message .= "🆘 <b>" . auth()->guard('admin')->user()->name . "</b> ";
                            $message .= "<b>" . $model->id . "</b> id nömrəli <b>" . $reflect->getShortName() . "</b> sildi.";

                            sendTGMessage($message);
                        }
                    }

                    if (isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP'])) {
                        $ip = $_SERVER['HTTP_CLIENT_IP'];
                    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                    } else {
                        $ip = (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : null;
                    }

                    /*if ($model->id == '6941') {
                        dd($_SERVER);
                    }*/
                    $content_id = $model->id;
                    $content_type = get_class($model);
                    $worker_id = auth()->guard('worker')->check() ? auth()->guard('worker')->user()->id : null;
                    $courier_id = auth()->guard('courier')->check() ? auth()->guard('courier')->user()->id : null;
		    $admin_id =  auth()->guard('admin')->check() ? auth()->guard('admin')->user()->id : null;
		    $user_id = NULL;
		    if(!$courier_id && !$admin_id) $user_id=auth()->check() ? auth()->user()->id : null;
                    $activity = Activity::create([
                        'admin_id' => $admin_id, 
                        'user_id'      => $user_id,
                        'courier_id'      => $courier_id,
                        'worker_id' => $worker_id,
                        'content_id' => $content_id,
                        'content_type' => $content_type,
                        'action' => static::getActionName($eventName),
                        'description' => ucfirst($eventName) . " a " . $reflect->getShortName(),
                        'details' => json_encode($model->getDirty()),
                        'ip' => $ip,
                        'user_agent' => (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : null,
                    ]);
                    if ($content_type == 'App\Models\Package' && $worker_id && $content_id) {
                        $act_work = DB::select("select * from activity_worker where content_id=?", [$content_id]);
                        if (!$act_work) {
                            DB::insert("insert into activity_worker(content_id,worker_id) values(?,?)", [$content_id, $worker_id]);
                        }
                    }
                    return $activity;
                } catch (Exception $e) {
                    //dd($e);

                    return true;
                }
            });
        }
    }

    /**
     * Set the default events to be recorded if the $recordEvents
     * property does not exist on the model.
     *
     * @return array
     */
    protected static function getRecordActivityEvents()
    {
        if (isset(static::$recordEvents)) {
            return static::$recordEvents;
        }

        return [
            'created',
            'updated',
            'deleted',
        ];
    }

    /**
     * Return Suitable action name for Supplied Event
     *
     * @param $event
     * @return string
     */
    protected static function getActionName($event)
    {
        switch (strtolower($event)) {
            case 'created':
                return 'create';
                break;
            case 'updated':
                return 'update';
                break;
            case 'deleted':
                return 'delete';
                break;
            default:
                return 'unknown';
        }
    }
}
