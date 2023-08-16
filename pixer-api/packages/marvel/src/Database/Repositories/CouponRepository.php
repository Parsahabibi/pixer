<?php


namespace Marvel\Database\Repositories;

use Marvel\Database\Models\Coupon;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Marvel\Database\Models\Settings;
use Marvel\Enums\CouponType;

class CouponRepository extends BaseRepository
{

    /**
     * @var array
     */
    protected $fieldSearchable = [
        'code'        => 'like',
        'language',

    ];

    public function boot()
    {
        try {
            $this->pushCriteria(app(RequestCriteria::class));
        } catch (RepositoryException $e) {
            //
        }
    }
    /**
     * Configure the Model
     **/
    public function model()
    {
        return Coupon::class;
    }

    public function verifyCoupon($code, $sub_total)
    {
        try {
            $coupon = $this->findOneByFieldOrFail('code', $code);
            $is_satisfy = $sub_total >= $coupon->minimum_cart_amount;
            $settings = Settings::getData();
            $is_freeShipping = $settings['options']['freeShipping'];
            $freeShippingAmount = $settings['options']['freeShippingAmount'];

            if ($coupon->is_valid && $is_freeShipping && $freeShippingAmount <= $sub_total && $coupon->type == CouponType::FREE_SHIPPING_COUPON) {
                return  ["is_valid" => false, "message" => ALREADY_FREE_SHIPPING_ACTIVATED];
            } else if ($coupon->is_valid &&  $is_satisfy) {
                return  ["is_valid" => true, "coupon" => $coupon];
            } else if ($coupon->is_valid && !$is_satisfy) {
                return  ["is_valid" => false, "message" => COUPON_CODE_IS_NOT_APPLICABLE];
            }
            return  ["is_valid" => false, "message" => INVALID_COUPON_CODE];
        } catch (\Exception $th) {
            return  ["is_valid" => false, "message" => INVALID_COUPON_CODE];
        }
    }
}
