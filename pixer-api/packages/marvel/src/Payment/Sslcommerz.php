<?php

namespace Marvel\Payments;

use Exception;
use Marvel\Database\Models\Order;
use Marvel\Enums\OrderStatus;
use Marvel\Enums\PaymentStatus;
use Marvel\Exceptions\MarvelException;
use Marvel\Traits\PaymentTrait;
use Razorpay\Api\Errors\SignatureVerificationError;
use DGvai\SSLCommerz\SSLCommerz as SslCommerzClient;
use Throwable;

class Sslcommerz extends Base implements PaymentInterface
{
    use PaymentTrait;

    protected $sslCommerzClient;

    /**
     * @throws Throwable
     */
    public function __construct()
    {
        parent::__construct();
        /* Creating a new instance of the class `SslCommerzClient` */
        $this->sslCommerzClient = new SslCommerzClient();
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
        try {
            extract($data);
            $faker  = $this->getCustomerParams();
            $order  = Order::where('tracking_number', $order_tracking_number)->OrWhere('id', $order_tracking_number)->first();

            /* Creating a new order. */
            $orderIntentJson = $this->sslCommerzClient
                ->amount($amount)
                ->trxid($order_tracking_number)
                ->product('Marvel_product')
                ->customer(
                    $order->customer_name ?? $faker['name'],
                    $order->customer->email ?? $faker['email'],
                    $order->customer_contact ?? $faker['mobile'],
                )
                ->setUrl($this->generateUrl($order_tracking_number))
                ->make_payment(true);
            /* Decoding the JSON response from the API. */
            $orderIntent = json_decode($orderIntentJson, true);
            return [
                'redirect_url' => $orderIntent['data'],
                'payment_id'   => $order_tracking_number,
                'is_redirect'  => true
            ];
        } catch (Exception $e) {
            throw new Exception(SOMETHING_WENT_WRONG_WITH_PAYMENT);
        }
    }


    /**
     * After payment verify that payment
     *
     * @param $order_tracking_number
     * @return mixed
     * @throws Throwable
     */
    public function verify($order_tracking_number): mixed
    {
        try {
            $result = $this->sslCommerzClient->query_transaction($order_tracking_number);
            /* Getting the last element of the array. */
            $lastElement = end($result->output->element);
            /* Checking if the status is set or not. If it is set, it will return the status, otherwise
            it will return false. */
            return isset($lastElement->status) ? $lastElement->status : false;
        } catch (Exception $e) {
            throw new MarvelException(SOMETHING_WENT_WRONG_WITH_PAYMENT);
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
        $validatePayment = $this->sslCommerzClient->validate_payment($request);
        switch ($validatePayment) {
            case true:
                $this->updatePaymentOrderStatus($request, OrderStatus::PROCESSING, PaymentStatus::SUCCESS);
                break;
            case false:
                $this->updatePaymentOrderStatus($request, OrderStatus::PENDING, PaymentStatus::FAILED);
                break;
        }
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
        $trackingId = $request["tran_id"];
        $order = Order::where('tracking_number', '=', $trackingId)->first();
        $this->webhookSuccessResponse($order, $orderStatus, $paymentStatus);
    }


    /**
     * It returns an array of customer data
     * 
     * @return array An array of data.
     */
    private function getCustomerParams(): array
    {
        return [
            'email'  => 'antonymous@mail.com',
            'mobile' => rand(10000000000, 99999999999),
            'name'   => 'Antonymous',
        ];
    }
    /**
     * It takes an order tracking number and returns an array of URLs
     * 
     * @param string|int This is the order tracking number that you will use to identify the
     * order.
     * 
     * @return array An array of strings.
     */
    private function generateUrl($order_tracking_umber): array
    {
        $shopUrl = config('shop.shop_url');
        $success = "{$shopUrl}/orders/{$order_tracking_umber}/thank-you";
        $failure = "{$shopUrl}/orders/{$order_tracking_umber}/thank-you";
        $cancel  = "{$shopUrl}/orders/{$order_tracking_umber}/thank-you";
        $ipn = route('sslc.sslcommerz');
        return [
            $success,
            $failure,
            $cancel,
            $ipn,
        ];
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
}
