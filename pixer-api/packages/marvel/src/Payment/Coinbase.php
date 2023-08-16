<?php

namespace Marvel\Payments;

use Exception;
use Illuminate\Support\Facades\Http;
use Marvel\Database\Models\Order;
use Marvel\Enums\OrderStatus;
use Marvel\Enums\PaymentStatus;
use Marvel\Traits\PaymentTrait;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
use Illuminate\Support\Str;
use Marvel\Database\Models\PaymentIntent;

class Coinbase extends Base implements PaymentInterface
{
    use PaymentTrait;

    protected $coinbaseClient;
    private array $header;
    /**
     * @throws Throwable
     */
    public function __construct()
    {
        parent::__construct();

        $this->header = [
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
            'X-CC-Version' => '2018-03-22',
            'X-CC-Api-Key' => config('shop.coinbase.api_key'),
        ];
    }


    /**
     * getIntent
     *
     * @param  mixed  $data
     * @return array
     * @throws Throwable
     */
    public function getIntent($data): array
    {
        $url = 'https://api.commerce.coinbase.com/charges';
        try {
            $response = Http::acceptJson()->withHeaders($this->header)->post($url, $this->getPostData($data));
            $order = $response['data'];
            return [
                'payment_id'   => $order["code"],
                'redirect_url' => $order['hosted_url'],
                'is_redirect'  => true
            ];
        } catch (Exception $e) {
            throw new HttpException(400, SOMETHING_WENT_WRONG_WITH_PAYMENT);
        }
    }

    /**
     * After payment verify that payment
     *
     * @param $id
     * @return mixed
     * @throws Throwable
     */
    public function verify($id): mixed
    {
        $url = "https://api.commerce.coinbase.com/charges/{$id}";
        $resolveUrl = "https://api.commerce.coinbase.com/charges/{$id}/resolve";
        try {
            $response = Http::acceptJson()->withHeaders($this->header)->get($url);
            $result = collect($response['data']['timeline'])->last();
            /* This code block checks if the status of the payment is 'Unresolved'. If it is, it sends
            a POST request to the Coinbase API to resolve the payment. It then updates the result
            variable with the last item in the timeline of the response data. */
            if (isset($result['status']) && $result['status'] == 'Unresolved') {
                $resolveResponse = Http::acceptJson()->withHeaders($this->header)->post($resolveUrl);
                $result = collect($resolveResponse['data']['timeline'])->last();
            }
            return isset($result['status']) ? $result['status'] : false;
        } catch (Exception $e) {
            throw new HttpException(400, SOMETHING_WENT_WRONG_WITH_PAYMENT);
        }
    }

    /**
     * handleWebHooks
     *
     * @param  mixed  $request
     * @return void
     * @throws Throwable
     */
    public function handleWebHooks($request): void
    {
        try {
            $trackingId = $request['event']['data']['code'];
            PaymentIntent::whereJsonContains('payment_intent_info->payment_id', $trackingId)->firstOrFail();
        } catch (Exception $e) {
            http_response_code(400);
            exit();
        }

        switch (strtolower($request['event']['type'])) {
            case "charge:created":
                $this->updatePaymentOrderStatus($request, OrderStatus::PENDING, PaymentStatus::PENDING);
                break;
            case "charge:pending":
                $this->updatePaymentOrderStatus($request, OrderStatus::PENDING, PaymentStatus::PENDING);
                break;
            case "charge:confirmed":
                $this->updatePaymentOrderStatus($request, OrderStatus::PROCESSING, PaymentStatus::SUCCESS);
                break;
            case "charge:failed":
                $this->updatePaymentOrderStatus($request, OrderStatus::CANCELLED, PaymentStatus::FAILED);
                break;
        }

        // To prevent loop for any case
        http_response_code(200);
        exit();
    }

    /**
     * Update Payment and Order Status
     *
     * @param $request
     * @param $orderStatus
     * @param $paymentStatus
     * @return void
     */
    public function updatePaymentOrderStatus($request, $orderStatus, $paymentStatus): void
    {
        $trackingId = $request['event']['data']['code'];
        $paymentIntent = PaymentIntent::whereJsonContains('payment_intent_info->payment_id', $trackingId)->firstOrFail();
        $order = Order::where('tracking_number', $paymentIntent->tracking_number)->firstOrFail();
        $this->webhookSuccessResponse($order, $orderStatus, $paymentStatus);
    }

    /**
     * The function returns an array of data for a payment request, including the name, description,
     * pricing, metadata, and redirect/cancel URLs.
     * 
     * @param array data An array of data that contains the necessary information to create a payment
     * request.
     * 
     * @return array An array of data for a payment request, including the name, description, pricing
     * type, local price, metadata, redirect URL, and cancel URL.
     */
    private function getPostData(array $data): array
    {
        extract($data);
        $redirectUrl = config('shop.shop_url');
        return [
            "name" => config('app.name') . ' Order no: ' . $order_tracking_number,
            "description" => 'Pay in Cryptocurrency',
            "pricing_type" => "fixed_price",
            "local_price" => [
                "currency" => $this->currency,
                "amount" => $amount
            ],
            "metadata" => [
                "customer_id" => auth()?->user()?->id ?? Str::uuid(),
                "customer_name" => $user_email ?? 'anonymous',
            ],
            "redirect_url" => "{$redirectUrl}/orders/{$order_tracking_number}/thank-you",
            "cancel_url"   => "{$redirectUrl}/orders/{$order_tracking_number}/payment"
        ];
    }

    /**
     * createCustomer
     *
     * @param  mixed  $request
     * @return array
     */
    public function createCustomer($request): array
    {
        return [];
    }

    /**
     * attachPaymentMethodToCustomer
     *
     * @param  string  $retrieved_payment_method
     * @param  object  $request
     * @return object
     */
    public function attachPaymentMethodToCustomer($retrieved_payment_method, $request): object
    {
        return (object) [];
    }

    /**
     * detachPaymentMethodToCustomer
     *
     * @param  string  $retrieved_payment_method
     * @return object
     */
    public function detachPaymentMethodToCustomer($retrieved_payment_method): object
    {
        return (object) [];
    }

    /**
     * retrievePaymentIntent
     *
     * @param $data
     * @return object
     */
    public function retrievePaymentIntent($data): object
    {
        return (object) [];
    }

    /**
     * confirmPaymentIntent
     *
     * @param  string  $payment_intent_id
     * @param  array  $data
     * @return object
     */
    public function confirmPaymentIntent($payment_intent_id, $data): object
    {
        return (object) [];
    }

    /**
     * setIntent
     *
     * @param  array  $data
     * @return array
     */
    public function setIntent($data): array
    {
        return [];
    }

    /**
     * retrievePaymentMethod
     *
     * @param  string  $method_key
     * @return object
     */
    public function retrievePaymentMethod($method_key): object
    {
        return (object) [];
    }
}
