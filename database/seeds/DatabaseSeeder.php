<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Models\Admin::class, 1)->create();
        factory(App\Models\Faq::class, 15)->create();
        factory(App\Models\City::class, 3)->create();
        factory(App\Models\Page::class, 10)->create();
        factory(App\Models\PackageType::class, 10)->create();
        factory(App\Models\Category::class, 8)->create();
        factory(App\Models\Country::class, 4)->create();
        factory(App\Models\Warehouse::class, 1)->create();
        factory(App\Models\Address::class, 3)->create();
        factory(App\Models\User::class, 10)->create();

        factory(App\Models\Store::class, 16)->create()->each(function ($store) {
            $numbers = range(1, 7);
            shuffle($numbers);
            $categories =  array_slice($numbers, 0, rand(1, 4));
            $store->categories()->attach($categories);
        });

        factory(App\Models\Coupon::class, 20)->create()->each(function ($store) {
            $numbers = range(1, 7);
            shuffle($numbers);
            $categories =  array_slice($numbers, 0, rand(1, 4));
            $store->categories()->attach($categories);
        });

        factory(App\Models\Product::class, 20)->create()->each(function ($store) {
            $numbers = range(1, 7);
            shuffle($numbers);
            $categories =  array_slice($numbers, 0, rand(1, 4));
            $store->categories()->attach($categories);
        });

        factory(App\Models\Package::class, 50)->create();
        factory(App\Models\Slider::class, 5)->create();
        factory(App\Models\Setting::class, 1)->create();
        factory(App\Models\Order::class, 50)->create()->each(function ($order) {
            for($i = 0; $i <= rand(3, 7); $i++)
                $order->links()->save(factory(App\Models\Link::class)->make());
        });

        $this->call(LaratrustSeeder::class);
        $this->call(TemplatesSeeder::class);
    }
}
