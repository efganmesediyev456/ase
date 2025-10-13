<?php

namespace App\Models\Extra;

use App\Models\EmailTemplate;
use App\Models\NotificationQueue;
use App\Models\User;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

/**
 * App\Models\Extra\Email
 *
 * @mixin Eloquent
 * @method static Builder|Email newModelQuery()
 * @method static Builder|Email newQuery()
 * @method static Builder|Email query()
 */
class Email extends Model
{
    public static function sendByQueue(NotificationQueue $queue)
    {
        if ($queue && $queue->type == 'EMAIL') {
            $newData = [
                'email' => $queue->to,
                'subject' => $queue->subject,
                'content' => $queue->content,
            ];

            @Mail::send('front.mail.notification', $newData, function ($message) use ($newData) {
                $message->from('noreply@' . env('DOMAIN_NAME'), env('APP_NAME'));
                $message->to($newData['email']);
                $message->subject($newData['subject']);
            });

            return true;
        }

        return false;
    }

    public static function sendByUser($userId, $data, $templateKey, $templateKey1 = null)
    {
        $template = null;
        if ($templateKey1)
            $template = EmailTemplate::where('key', $templateKey1)->where('active', 1)->first();
        if (!$template)
            $template = EmailTemplate::where('key', $templateKey)->where('active', 1)->first();

        $user = User::find($userId);

        if (!$template || !$user || !$user->email) {
            return false;
        }

        $content = clarifyContent($template->content, $data);

        $id = (isset($data['id']) ? $data['id'] : uniqid());

        NotificationQueue::create([
            'to' => $user->email,
            'subject' => $template->name . " #" . $id,
            'content' => $content,
            'type' => 'EMAIL',
            'send_for_id' => $id,
        ]);

        return true;
    }

    public static function sendByAddress($email, $data, $templateKey, $templateKey1 = null)
    {
        $template = null;
        if ($templateKey1)
            $template = EmailTemplate::where('key', $templateKey1)->where('active', 1)->first();
        if (!$template)
            $template = EmailTemplate::where('key', $templateKey)->where('active', 1)->first();

        if (!$template) {
            return false;
        }

        $content = clarifyContent($template->content, $data);

        NotificationQueue::create([
            'to' => $email,
            'subject' => $template->name . " #" . (isset($data['id']) ? $data['id'] : uniqid()),
            'content' => $content,
            'type' => 'EMAIL',
        ]);

        return true;
    }

    public static function sendToAllUsers($data, $templateKey, $templateKey1 = null)
    {
        $template = null;
        if ($templateKey1)
            $template = EmailTemplate::where('key', $templateKey1)->where('active', 1)->first();
        if (!$template)
            $template = EmailTemplate::where('key', $templateKey)->where('active', 1)->first();

        if (!$template) {
            return false;
        }

        $users = User::all();
        $content = clarifyContent($template->content, $data);

        $newData = [
            'content' => $content,
        ];

        foreach ($users as $user) {
            @Mail::send('front.mail.notification', $newData, function ($message) use ($newData, $user) {
                $message->from('noreply@' . env('DOMAIN_NAME'));
                $message->to($user->email);
                $message->subject($newData['content']);
            });
        }

        return true;
    }
}
