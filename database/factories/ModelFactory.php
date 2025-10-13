<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Models\Admin::class, function (Faker\Generator $faker) {
    return [
        'name'           => $faker->name,
        'email'          => 'test@ase.az',
        'password'       => 'secret',
        'remember_token' => str_random(10),
    ];
});

/*
 * Page
 * */
$factory->define(App\Models\Page::class, function (Faker\Generator $faker) {
    return [
        'title'         => $faker->sentence(2),
        'type'          => rand(0, 1),
        'keyword'       => 'page-' . uniqid(),
        'content'       => $faker->realText(),
        'meta_keywords' => implode(",", $faker->words(4)),
    ];
});

/*
 * FAQ
 * */
$factory->define(App\Models\Faq::class, function (Faker\Generator $faker) {
    return [
        'question' => $faker->sentence(2),
        'answer'   => $faker->text(),
    ];
});

/*
 * Package Type
 * */
$factory->define(App\Models\PackageType::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->word,
    ];
});

/*
 * Categories
 * */
$factory->define(App\Models\Category::class, function (Faker\Generator $faker) {
    return [
        'name'        => $faker->word,
        'description' => $faker->sentence(3),
    ];
});

/*
 * Categories
 * */
$factory->define(App\Models\Country::class, function (Faker\Generator $faker) {
    return [
        'code'           => $faker->countryCode,
        'name'           => $faker->country,
        'emails'         => 'gularak@ase.az, zaka@ase.az, anastasiyas@ase.az',
        'delivery_index' => 6000,
    ];
});

/*
 * Warehouses
 * */
$factory->define(App\Models\Warehouse::class, function (Faker\Generator $faker) {
    return [
        'country_id'     => rand(1, 4),
        'company_name'   => $faker->company,
        'email'          => $faker->unique()->safeEmail,
        'password'       => 'secret',
        'half_kg'        => 4,
        'per_kg'         => 6.5,
        'up_10_kg'       => 6,
        'key'            => 'SDf3459s@34sfd',
    ];
});

/*
 * Warehouses
 * */
$factory->define(App\Models\Address::class, function (Faker\Generator $faker) {
    return [
         'warehouse_id'   => 1,
         'contact_name'   => $faker->name,
         'address_line_1' => $faker->address,
         'phone'          => $faker->phoneNumber,
         'city'           => $faker->city,
         'state'          => $faker->city,
         'zip_code'       => $faker->postcode,
    ];
});

/*
 * Users
 * */
$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
    $cityId = rand(0, 3);

    return [
        'name'        => $faker->firstName,
        'surname'     => $faker->lastName,
        'passport'    => $faker->creditCardNumber,
        'address'     => $faker->address,
        'phone'       => $faker->phoneNumber,
        'customer_id' => 'ASE-' . uniqid(),
        'city'        => $faker->city,
        'city_id'     => $cityId ?: null,
        'zip_code'    => $faker->postcode,
        'email'       => $faker->unique()->safeEmail,
        'password'    => 'secret',
    ];
});

/*
 * Stores
 * */
$factory->define(App\Models\Store::class, function (Faker\Generator $faker) {
    return [
        'url'         => $faker->url,
        'name'        => $faker->company,
        'sale'        => rand(3, 19),
        'description' => $faker->text,
    ];
});

/*
 * Coupons
 * */
$factory->define(App\Models\Coupon::class, function (Faker\Generator $faker) {
    return [
        'store_id'    => rand(1, 10),
        'url'         => $faker->url,
        'name'        => $faker->sentence(2),
        'description' => $faker->text,
        'code'        => uniqid(),
        //'image' => $faker->imageUrl(),
    ];
});

/*
 * Coupons
 * */
$factory->define(App\Models\Product::class, function (Faker\Generator $faker) {
    return [
        'store_id'    => rand(1, 10),
        'url'         => $faker->url,
        'name'        => $faker->sentence(2),
        'description' => $faker->text,
        'old_price'   => '$' . rand(30, 200),
        'sale'        => '5%',
        'price'       => '$' . rand(30, 200),
    ];
});

/*
 * Package
 * */
$factory->define(App\Models\Package::class, function (Faker\Generator $faker) {
    $webSites = [
        'http://amazon.com',
        'ebay.com',
        'tozlu.com',
        'asos.com',
        'alibaba.com',
    ];

    return [
        'user_id'         => rand(1, 10),
        'warehouse_id'    => 1,
        'custom_id'       => uniqid(),
        'weight'          => rand(1, 5),
        'weight_type'     => rand(0, 2),
        'width'           => 50,
        'height'          => 60,
        'length'          => 30,
        'tracking_code'   => uniqid() . 'Ht-' . uniqid(),
        'website_name'    => $webSites[rand(0, 4)],
        'number_items'    => rand(1, 8),
        'shipping_amount' => rand(5, 15),
        'status'          => rand(0, 4),
    ];
});

/*
 * Slider
 * */
$factory->define(App\Models\Slider::class, function (Faker\Generator $faker) {
    return [
        'name'         => $faker->sentence,
        'title'        => $faker->sentence(4),
        'content'      => $faker->text,
        'button_label' => 'View',
        'url'          => $faker->url,
    ];
});

/*
 * Request
 * */
$factory->define(App\Models\Order::class, function (Faker\Generator $faker) {
    return [
        'user_id'    => rand(1, 5),
        'custom_id'  => uniqid(),
        'note'       => $faker->sentence,
        'country_id' => rand(1, 4),
    ];
});

$factory->define(App\Models\Link::class, function (Faker\Generator $faker) {
    return [
        'url' => $faker->url,
    ];
});

$factory->define(App\Models\Setting::class, function (Faker\Generator $faker) {
    return [
        'address'  => $faker->address,
        'facebook' => 'https://facebook.com',
        'twitter'  => 'https://twitter.com',
        'email'    => $faker->email,
        'phone'    => $faker->phoneNumber,
    ];
});

/*
 * City
 * */
$factory->define(App\Models\City::class, function (Faker\Generator $faker) {
    return [
        'name'    => $faker->city,
        'address' => $faker->sentence(3),
    ];
});
