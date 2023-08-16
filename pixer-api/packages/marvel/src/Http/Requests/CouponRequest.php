<?php

namespace Marvel\Http\Requests;

use Illuminate\Validation\Rule;
use Marvel\Enums\CouponType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class CouponRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $language = $this->language ?? DEFAULT_LANGUAGE;
        if ($this->has('type') && $this->type == 'percentage') {
            $rules['amount'] = ['required', 'numeric', 'min:0', 'max:100'];
        } else {
            $rules['amount'] = ['required', 'numeric', 'min:0'];
        }

        return  [
            'code'                => ['required', Rule::unique('coupons')->where('language', $language)],
            'amount'              => $rules['amount'],
            'minimum_cart_amount' => ['required', 'numeric', 'min:0'],
            'type'                => ['required', Rule::in([CouponType::FIXED_COUPON, CouponType::PERCENTAGE_COUPON, CouponType::FREE_SHIPPING_COUPON])],
            'description'         => ['nullable', 'string'],
            'image'               => ['array',],
            'language'            => ['nullable', 'string'],
            'active_from'         => ['required', 'date'],
            'expire_at'           => ['required', 'date'],
            'language'            => ['nullable', 'string'],
        ];
    }

    /**
     * Get the error messages that apply to the request parameters.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'code.required'                => 'Code field is required and it should be unique',
            'amount.required'              => 'Amount field is required',
            'minimum_cart_amount.required' => 'Cart Minimum Amount field is required',
            'type.required'                => 'Coupon type is required and it can be only ' . CouponType::FIXED_COUPON . ' or ' . CouponType::PERCENTAGE_COUPON . ' or ' . CouponType::FREE_SHIPPING_COUPON . '',
            'type.in'                      => 'Type only can be ' . CouponType::FIXED_COUPON . ' or ' . CouponType::PERCENTAGE_COUPON . ' or ' . CouponType::FREE_SHIPPING_COUPON . '',
            'active_from.required'         => 'Active from field is required',
            'expire_at.required'           => 'Expire at field is required',
        ];
    }


    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
