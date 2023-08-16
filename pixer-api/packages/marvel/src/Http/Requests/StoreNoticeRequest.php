<?php

namespace Marvel\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Marvel\Enums\Permission;
use Marvel\Enums\StoreNoticePriority;
use Marvel\Enums\StoreNoticeType;

class StoreNoticeRequest extends FormRequest
{
    /**
     * Rule Variable
     *
     * @var array
     */
    protected $rules = [];

    /**
     * array Store notice type
     *
     * @var array $typeArr
     */
    protected $typeArr = [
        StoreNoticeType::ALL_VENDOR,
        StoreNoticeType::SPECIFIC_VENDOR,
        StoreNoticeType::ALL_SHOP,
        StoreNoticeType::SPECIFIC_SHOP
    ];

    /**
     * array Store notice Priority
     *
     * @var array $priorityArr
     */
    protected $priorityArr = [StoreNoticePriority::HIGH, StoreNoticePriority::MEDIUM, StoreNoticePriority::LOW];

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
        return [
            'priority'       => ['required', 'string', Rule::in($this->priorityArr)],
            'notice'         => ['required', 'string'],
            'description'    => ['nullable', 'string'],
            'effective_from' => ['nullable', 'date'],
            'expired_at'     => ['required', 'date','after:effective_from'],
            'type'           => ['required', 'string', Rule::in($this->typeArr)],
            'received_by'    => ['array','required_if: type,' . StoreNoticeType::SPECIFIC_VENDOR . ',' . StoreNoticeType::SPECIFIC_SHOP],
            'received_by.*'  => ['nullable', 'integer']
        ];
    }

    /**
     * Get the validation custom messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'received_by.required_if' => 'Please! Select at least one Specific receiver.'
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @return void
     */
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
