<?php

namespace Marvel\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Marvel\Enums\OrderStatus;

class OrderUpdateRequest extends FormRequest
{
    protected $rules = [];

    /**
     * General validation rules
     *
     * @return array
     */
    protected function getRules()
    {
        return [
            'coupon_id'       => 'nullable|exists:Marvel\Database\Models\Coupon,id',
            'shop_id'         => 'exists:Marvel\Database\Models\Shop,id',
            'products'        => 'array',
            'amount'          => 'numeric',
            'paid_total'      => 'numeric',
            'total'           => 'numeric',
            // 'status'       => 'exists:Marvel\Database\Models\OrderStatus,id',
            'order_status'    => ['required', Rule::in([
                OrderStatus::PROCESSING,
                OrderStatus::COMPLETED,
                OrderStatus::AT_LOCAL_FACILITY,
                OrderStatus::OUT_FOR_DELIVERY,
                OrderStatus::CANCELLED
            ])],
            'customer_id'     => 'exists:Marvel\Database\Models\User,id',
            'payment_gateway' => 'string',
        ];
    }

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
        $this->rules = $this->getRules();
        return $this->rules;
    }


    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
