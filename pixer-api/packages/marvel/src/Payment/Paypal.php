<?php

namespace Marvel\Payments;

use Exception;
use Marvel\Database\Models\Order;
use Marvel\Enums\OrderStatus;
use Marvel\Enums\PaymentStatus;
use Marvel\Traits\PaymentTrait;
use Razorpay\Api\Errors\SignatureVerificationError;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Str;
use Throwable;

class Paypal extends Base implements PaymentInterface
{
    use PaymentTrait;

    protected PayPalClient $paypalClient;

    /**
     * @throws Throwable
     */
    public function __construct()
    {
        parent::__construct();

        // Initialize paypal client
        $this->paypalClient = new PayPalClient(config('shop.paypal'));
        $token = $this->paypalClient->getAccessToken();
        $this->paypalClient->setAccessToken($token);
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
            $redirectUrl = config('shop.shop_url');
            $this->paypalClient->setRequestHeader("PayPal-Request-Id", Str::uuid());
            $order = $this->paypalClient->createOrder([
                "intent"         => "CAPTURE",
                "purchase_units" => [
                    [
                        "invoice_id"  => $order_tracking_number,
                        "amount"      => [
                            "currency_code" => $this->currency,
                            "value"         => round($amount, 2),
                        ],
                        'description' => "Order From " . config('app.name'),
                    ]
                ],

                "payment_source" => [
                    "paypal" => [
                        "experience_context" => [
                            'user_action'               => 'PAY_NOW',
                            'payment_method_preference' => 'IMMEDIATE_PAYMENT_REQUIRED',
                            'cancel_url'                => "{$redirectUrl}/orders/{$order_tracking_number}/payment",
                            'return_url'                => "{$redirectUrl}/orders/{$order_tracking_number}/thank-you"
                        ]
                    ]
                ],
            ]);
            return ['redirect_url' => $order['links'][1]['href'], 'payment_id' => $order["id"], 'is_redirect' => true];
        } catch (Exception $e) {
            throw new HttpException(400, SOMETHING_WENT_WRONG_WITH_PAYMENT);
        }
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
     * After payment verify that payment
     *
     * @param $id
     * @return mixed
     * @throws Throwable
     */
    public function verify($id): mixed
    {
        try {
            $result = $this->paypalClient->capturePaymentOrder($id);
            return isset($result["status"]) ? $result : false;
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
        $webhookId = config('shop.paypal.webhook_id');
        // gather webhook data to verify it
        $verifyData = [
            'auth_algo'         => $request->header('PAYPAL-AUTH-ALGO', null),
            'cert_url'          => $request->header('PAYPAL-CERT-URL', null),
            'transmission_id'   => $request->header('PAYPAL-TRANSMISSION-ID', null),
            'transmission_sig'  => $request->header('PAYPAL-TRANSMISSION-SIG', null),
            'transmission_time' => $request->header('PAYPAL-TRANSMISSION-TIME', null),
            'webhook_id'        => $webhookId,
            'webhook_event'     => $request->all()
        ];

        // Verify webhook
        try {
            if ($webhookId && $verifyData) {
                $event = $this->paypalClient->verifyWebHook($verifyData);
                if ($event["verification_status"] !== "SUCCESS") {
                    // Invalid verification status
                    http_response_code(400);
                    exit();
                }
            } else {
                // Invalid request
                http_response_code(400);
                exit();
            }
        } catch (SignatureVerificationError $e) {
            // Invalid signature
            http_response_code(400);
            exit();
        }

        switch ($request->event_type) {
            case "PAYMENT.CAPTURE.COMPLETED":
                $this->updatePaymentOrderStatus($request, OrderStatus::PROCESSING, PaymentStatus::SUCCESS);
                break;
            case "PAYMENT.CAPTURE.PENDING":
                $this->updatePaymentOrderStatus($request, OrderStatus::PENDING, PaymentStatus::PENDING);
                break;
            case "PAYMENT.CAPTURE.CANCELLED":
                $this->updatePaymentOrderStatus($request, OrderStatus::PENDING, PaymentStatus::FAILED);
                break;
            case "PAYMENT.CAPTURE.REVERSED":
                $this->updatePaymentOrderStatus($request, OrderStatus::CANCELLED, PaymentStatus::REVERSAL);
                break;
        }

        // To prevent loop for any case
        http_response_code(200);
        exit();
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
     * Update Payment and Order Status
     *
     * @param $request
     * @param $orderStatus
     * @param $paymentStatus
     * @return void
     */
    public function updatePaymentOrderStatus($request, $orderStatus, $paymentStatus): void
    {
        $trackingId = $request->resource["invoice_id"];
        $order = Order::where('tracking_number', '=', $trackingId)->first();
        $this->webhookSuccessResponse($order, $orderStatus, $paymentStatus);
    }
}
