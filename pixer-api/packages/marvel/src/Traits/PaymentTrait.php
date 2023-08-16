<?php

namespace Marvel\Traits;

use Exception;
use Illuminate\Http\Request;
use Marvel\Database\Models\Order;
use Marvel\Database\Models\PaymentGateway;
use Marvel\Database\Models\Settings;
use Marvel\Exceptions\MarvelException;
use Marvel\Database\Models\PaymentMethod;
use Marvel\Database\Models\PaymentIntent;
use Marvel\Enums\PaymentGatewayType;
use Marvel\Events\PaymentMethods;
use Marvel\Facades\Payment;
use Symfony\Component\HttpKernel\Exception\HttpException;

trait PaymentTrait
{
    /**
     * processPaymentIntent
     *
     * @param  mixed  $request
     * @param  mixed  $settings
     * @return object
     * @throws Exception
     */
    public function processPaymentIntent(Request $request, Settings $settings): object
    {
        $orderTrackingNumber = $request['tracking_number'];
        $order = $this->fetchOrderByTrackingNumber($orderTrackingNumber);

        $isPaymentIntentExists = $this->paymentIntentExists($orderTrackingNumber, $settings->options['paymentGateway']);
        if (!$isPaymentIntentExists) {
            return $this->savePaymentIntent($order, $settings->options['paymentGateway'], $request);
        }

        return PaymentIntent::where('tracking_number', '=', $orderTrackingNumber)->orWhere('order_id', '=', $orderTrackingNumber)->where('payment_gateway', '=', strtoupper($settings->options['paymentGateway']))->first();
    }


    /**
     * paymentIntentExists
     *
     * @param  string $order_tracking_number
     * @param  string $payment_gateway
     * @return bool
     */
    public function paymentIntentExists(string $order_tracking_number, string $payment_gateway): bool
    {
        return PaymentIntent::where('tracking_number', '=', $order_tracking_number)->orWhere('order_id', '=', $order_tracking_number)->where('payment_gateway', '=', strtoupper($payment_gateway))->exists();
    }


    /**
     * savePaymentIntent
     *
     * @param  mixed $order
     * @param  mixed $payment_gateway
     * @param  mixed $request
     * @return object
     */
    public function savePaymentIntent($order, $payment_gateway, $request)
    {
        return PaymentIntent::create([
            'order_id'            => $order->id,
            "tracking_number"     => $order->tracking_number,
            "payment_gateway"     => $payment_gateway,
            "payment_intent_info" => $this->createPaymentIntent($order, $request, $payment_gateway),
        ]);
    }


    /**
     * createPaymentIntent
     *
     * @param  mixed  $order
     * @param  mixed  $request
     * @param  string $payment_gateway
     * @return array
     * @throws Exception
     */
    public function createPaymentIntent(Order $order, Request $request, string $payment_gateway): array
    {
        // Make it automated in future
        $created_intent = [
            "amount"                => $order->paid_total,
            "order_tracking_number" => $order->tracking_number,
        ];
        if ($request->user() !== null) {
            $created_intent["user_email"] = $order->customer->email;
        }

        if ($request->user() !== null) {
            $created_intent["user_email"] = $order->customer->email;
        }

        if ($request->user() !== null && strtoupper($payment_gateway) === PaymentGatewayType::STRIPE) {
            $customer = $this->createPaymentCustomer($request);
            $created_intent["customer"] = $customer["customer_id"];
        }
        if(strtoupper($payment_gateway) === PaymentGatewayType::IYZICO){
            $created_intent["ip"] = $request->ip();
        }

        return Payment::getIntent($created_intent);
    }


    /**
     * findByTrackingNumber
     *
     * @param  string $tracking_number
     * @return object
     */
    public function fetchOrderByTrackingNumber($tracking_number)
    {
        try {
            return Order::where('id', "=", $tracking_number)->orWhere('tracking_number', $tracking_number)->first();
        } catch (\Exception $e) {
            throw new HttpException(404, NOT_FOUND);
        }
    }

    /**
     * saveCard
     *
     * @param  mixed $payment_method
     * @param  mixed $request
     * @return PaymentMethod
     */
    public function saveCard($payment_method, $request): PaymentMethod
    {
        // brand & network are equivalent with razorpay & stripe, "network" in marvel DB
        // type & funding are equivalent with razorpay & stripe, "type" in marvel DB

        $settings = Settings::first();
        $customers_gateway = PaymentGateway::where('user_id', '=', $request->user()->id)->where('gateway_name', '=', $settings->options['paymentGateway'])->first();

        // if first card, then set as default.
        $default = false;
        if (is_null(PaymentMethod::first())) {
            $default = true;
        } else {
            $default = $request->default_card;
        }
        /* Updating the default card to false if the payment gateway is stripe. */
        if ($default) {
            PaymentMethod::where([
                "default_card"       => true,
                "payment_gateway_id" =>  $customers_gateway->id,
            ])->update(['default_card' => false]);
        }

        $payment_method = PaymentMethod::create([
            'method_key'         => $payment_method->id,
            "payment_gateway_id" => $customers_gateway->id,
            "default_card"       => $default,
            "fingerprint"        => $payment_method->card->fingerprint,
            "owner_name"         => $payment_method->billing_details->name,
            "last4"              => $payment_method->card->last4,
            "expires"            => $payment_method->card->exp_month . "/" . $payment_method->card->exp_year,
            "network"            => $payment_method->card->brand,
            "type"               => $payment_method->card->funding,
            "origin"             => $payment_method->card->country,
            "verification_check" => $payment_method->card->checks->cvc_check,
        ]);

        // run a job to check and set default card
        if ($request->default_card) {
            event(new PaymentMethods($payment_method));
        }

        return $payment_method;
    }

    /**
     * createPaymentCustomer
     *
     * @param  mixed $request
     * @return object
     */
    public function createPaymentCustomer($request)
    {
        try {
            $settings = Settings::first();
            if (!$this->customerAlreadyExists($request->user()->id, $settings)) {
                $customer = Payment::createCustomer($request);
                if (in_array(strtoupper($settings->options['paymentGateway']), PaymentGatewayType::getValues())) {
                    PaymentGateway::create([
                        'user_id'      => $request->user()->id,
                        'customer_id'  => $customer['customer_id'],
                        'gateway_name' => $settings->options['paymentGateway']
                    ]);
                }
            } else {
                $customer = PaymentGateway::where('user_id', '=', $request->user()->id)->where('gateway_name', '=', $settings->options['paymentGateway'])->first();
            }
            return $customer;
        } catch (\Exception $e) {
            throw new HttpException(400, SOMETHING_WENT_WRONG);
        }
    }

    /**
     * customerAlreadyExists
     *
     * @param  string $user_id
     * @return boolean
     */
    public function customerAlreadyExists($user_id, $settings)
    {
        try {
            $customer_exists = false;
            $customer_exists = PaymentGateway::where('user_id', '=', $user_id)->where('gateway_name', '=', $settings->options['paymentGateway'])->exists();
            if ($customer_exists) {
                return true;
            }
            return $customer_exists;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * paymentMethodAlreadyExists
     *
     * @param  string $fingerprint
     * @return bool
     */
    public function paymentMethodAlreadyExists($fingerprint)
    {
        try {
            $payment_method_exists = false;
            $payment_method_exists = PaymentMethod::where('fingerprint', '=', $fingerprint)->exists();
            if ($payment_method_exists) {
                return true;
            }
            return $payment_method_exists;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * webhookSuccessResponse
     *
     * @param  mixed  $order
     * @param  string $order_status
     * @param  string $payment_status
     * @return void
     */
    public function webhookSuccessResponse($order, $order_status, $payment_status)
    {
        $order->order_status = $order_status;
        $order->payment_status = $payment_status;
        $order->save();
        try {
            $children = json_decode($order->children);
        } catch (\Throwable $th) {
            $children = $order->children;
        }
        if (is_array($children) && count($children)) {
            foreach ($order->children as $child_order) {
                $child_order->order_status = $order_status;
                $child_order->payment_status = $payment_status;
                $child_order->save();
            }
        }
    }
}
