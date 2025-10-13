<?php

namespace App\Models;

use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Setting
 *
 * @property int $id
 * @property string|null $header_logo
 * @property string|null $footer_logo
 * @property string|null $email
 * @property string|null $location
 * @property string|null $address
 * @property string|null $phone
 * @property string|null $facebook
 * @property string|null $twitter
 * @property string|null $instagram
 * @property string|null $linkedin
 * @property string|null $about_cover
 * @property string|null $shop_cover
 * @property string|null $tariffs_cover
 * @property string|null $calculator_cover
 * @property string|null $faq_cover
 * @property string|null $contact_cover
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Setting whereAboutCover($value)
 * @method static Builder|Setting whereAddress($value)
 * @method static Builder|Setting whereCalculatorCover($value)
 * @method static Builder|Setting whereContactCover($value)
 * @method static Builder|Setting whereCreatedAt($value)
 * @method static Builder|Setting whereEmail($value)
 * @method static Builder|Setting whereFacebook($value)
 * @method static Builder|Setting whereFaqCover($value)
 * @method static Builder|Setting whereFooterLogo($value)
 * @method static Builder|Setting whereHeaderLogo($value)
 * @method static Builder|Setting whereId($value)
 * @method static Builder|Setting whereInstagram($value)
 * @method static Builder|Setting whereLinkedin($value)
 * @method static Builder|Setting whereLocation($value)
 * @method static Builder|Setting wherePhone($value)
 * @method static Builder|Setting whereShopCover($value)
 * @method static Builder|Setting whereTariffsCover($value)
 * @method static Builder|Setting whereTwitter($value)
 * @method static Builder|Setting whereUpdatedAt($value)
 * @mixin Eloquent
 * @method static Builder|Setting newModelQuery()
 * @method static Builder|Setting newQuery()
 * @method static Builder|Setting query()
 */
class Setting extends Model
{
    public $uploadDir = 'uploads/setting/';
}
