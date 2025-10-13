<?php

use Illuminate\Database\Seeder;

class TemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('s_m_s_templates')->insert([
            'key'     => 'registration',
            'name'    => 'Registration',
            'content' => 'Nomrenizi tesdiqlediyiniz uchun teshekkurler! Size xosh alish-verishler arzulayiriq!',
        ]);

        DB::table('s_m_s_templates')->insert([
            'key'     => 'sms_verify',
            'name'    => 'Sms Verification',
            'content' => 'Hormetli, :user . Sizin telefon nomresi uchun tesdiq ucun kodunuz :code',
        ]);

        DB::table('s_m_s_templates')->insert([
            'key'     => 'no_declaration',
            'name'    => 'No declaration',
            'content' => 'Hörmetli, :user . :track_code Track nomreli baglamaniz anbardadi. Xahish edilir deklarasiyani doldurlasiniz.',
        ]);
        DB::table('email_templates')->insert([
            'key'     => 'no_declaration',
            'name'    => 'No declaration',
            'content' => 'Hörmetli, :user . :track_code Track nomreli baglamaniz anbardadi. Xahish edilir deklarasiyani doldurlasiniz.',
        ]);


        //'status' => ['New Order', 'Paid', 'Ordered', 'Delivered', 'Canceled', 'Accepted'],
        $orders = [
            0 => [
                'title'   => 'New Order',
                'content' => 'Hörmetli, :user . :order_id id nomreli sifarishiniz qebul olundu. Tezlikle sizinle elaqe saxlanilacaq. Teshekkur edirik!',
            ],

            2 => [
                'title'   => 'Order Completed',
                'content' => 'Hörmetli, :user . :order_id id nomreli sifarishe daxil mehsul(lar) operator terefinden sifarish edildi. Teshekkur edirik!',
            ],
            3 => [
                'title'   => 'Order Delivered',
                'content' => 'Hörmetli, :user . :order_id id nomreli sifarishe daxil mehsul(lar) chatdirildi ve tamamlandi. Teshekkur edirik!',
            ],
            4 => [
                'title'   => 'Order Canceled',
                'content' => 'Hörmetli, :user . :order_id id nomreli sifarishiniz operator terefinden legv edildi.',
            ],
            5 => [
                'title'   => 'Order Proceeded',
                'content' => 'Hörmetli, :user . :order_id id nomreli sifarishiniz operator terefinden qeyde alindi. Sifarish process merlehesindedir.',
            ],
        ];

        foreach ($orders as $i => $value) {
            DB::table('s_m_s_templates')->insert([
                'key'     => 'order_status_' . $i,
                'name'    => $value['title'],
                'content' => $value['content'],
            ]);

            DB::table('email_templates')->insert([
                'key'     => 'order_status_' . $i,
                'name'    => $value['title'],
                'content' => $value['content'],
            ]);
        }

        $packages = [
            [
                'title'   => 'In warehouse',
                'content' => 'Hörmetli, :user . Sizin :track_code  track nömrəli bağlamanız qəbul olunub və hal-hazırda xarici anbarındadır. Siz həmçinin məlumatı '  . env('DOMAIN_NAME') . ' saytında yoxlaya bilərsiniz. Məktub avtomatik olaraq göndərilmişdir və cavab verməyə ehtiyac duyulmur. Hər hansı bir sual yaranarsa, info@camex.az elektron poçt ünvanına müraciət edə bilərsiniz!',
            ],
            [
                'title'   => 'Sent',
                'content' => 'Hörmetli, :user . Sizin :track_code  track nömrəli bağlamanız Bakı anbarına göndərildi.',
            ],
            [
                'title'   => 'In Baku',
                'content' => 'Hörmetli, :user . Sizin :track_code  track nömrəli bağlamanız hal hazırda Bakı anbarındadır. Yaxınlaşıb bağlamanı götürə bilərsiniz.',
            ],
            [
                'title'   => 'Done',
                'content' => 'Hörmetli, :user . Sizin :track_code  track nömrəli bağlamanız üzrə xidmət tamamlanmışdır.!',
            ],
            [
                'title'   => 'In customs',
                'content' => 'Hörmetli, :user . Sizin :track_code  track nömrəli bağlamanız hal hazırda gömrükdədir. Xahiş edilir əlaqə saxlayın.',
            ],
            [
                'title'   => 'Rejected',
                'content' => 'Hörmetli, :user . Sizin :track_code  track nömrəli bağlamanız ləğv edilmişdir.',
            ],
        ];

        foreach ($packages as $i => $value) {
            DB::table('s_m_s_templates')->insert([
                'key'     => 'package_status_' . $i,
                'name'    => $value['title'],
                'content' => $value['content'],
            ]);

            DB::table('email_templates')->insert([
                'key'     => 'package_status_' . $i,
                'name'    => $value['title'],
                'content' => $value['content'],
            ]);
        }
    }
}
