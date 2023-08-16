<?php

namespace Marvel\Payments;

use ErrorException;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Iyzipay\Model\Address as IyzicoAddress;
use Iyzipay\Model\BasketItem;
use Iyzipay\Model\BasketItemType;
use Iyzipay\Model\Buyer;
use Iyzipay\Model\CheckoutForm;
use Iyzipay\Model\Currency;
use Iyzipay\Model\Locale;
use Iyzipay\Model\PayWithIyzicoInitialize;
use Iyzipay\Options as IyzicoClient;
use Iyzipay\Request\CreatePayWithIyzicoInitializeRequest;
use Iyzipay\Request\RetrieveCheckoutFormRequest;
use Marvel\Database\Models\Order;
use Marvel\Database\Models\PaymentIntent;
use Marvel\Enums\OrderStatus;
use Marvel\Enums\PaymentStatus;
use Marvel\Traits\PaymentTrait;
use Throwable;

class Iyzico extends Base implements PaymentInterface
{
    use PaymentTrait;

    protected IyzicoClient $iyzicoClient;

    /**
     * @throws Throwable
     */
    public function __construct()
    {
        parent::__construct();

        // Initialize iyzico client
        $this->iyzicoClient = new IyzicoClient();
        $this->iyzicoClient->setApiKey(config('shop.iyzico.api_Key'));
        $this->iyzicoClient->setSecretKey(config('shop.iyzico.secret_Key'));
        $this->iyzicoClient->setBaseUrl(config('shop.iyzico.baseUrl'));
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

            $iyzicoOrder = $this->requestToIyzico($data);
            if ($iyzicoOrder->getStatus() == "failure") {
                throw new ErrorException($iyzicoOrder->getErrorMessage());
            }
            return [
                'payment_id' => $iyzicoOrder->getToken(),
                'is_redirect' => true,
                'redirect_url' => $iyzicoOrder->getPayWithIyzicoPageUrl(),
            ];
        } catch (Exception $e) {
            throw new Exception(SOMETHING_WENT_WRONG_WITH_PAYMENT);
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
        try {
            $request = new RetrieveCheckoutFormRequest();
            $request->setToken($id);
            $result = CheckoutForm::retrieve($request, $this->iyzicoClient);
            return $result->getPaymentStatus() ? $result->getPaymentStatus() : false;
        } catch (Exception $e) {
            throw new Exception(SOMETHING_WENT_WRONG_WITH_PAYMENT);
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
        $webhookSignature = $request->header('x-iyz-signature');
        $stringToBeHashed = base64_encode(sha1(config('shop.iyzico.secret_Key') . $request->iyziEventType . $request->token, true));
        try {
            $orderStatus = $this->verify($request?->token);
        } catch (Throwable $e) {
            $orderStatus = false;
        }
        // Verify webhook
        if ($stringToBeHashed == $webhookSignature || $orderStatus) {
            switch ($request->status) {
                case "SUCCESS":
                    $this->updatePaymentOrderStatus($request, OrderStatus::PROCESSING, PaymentStatus::SUCCESS);
                    break;
                case "FAILURE":
                    $this->updatePaymentOrderStatus($request, OrderStatus::PENDING, PaymentStatus::FAILED);
                    break;
            }
            // To prevent loop for any case
            http_response_code(200);
            exit();
        } else {
            // Invalid request
            http_response_code(400);
            exit();
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
        $token = $request?->token;
        $paymentGateway = PaymentIntent::where('payment_intent_info->payment_id', $token)->first();
        $order = Order::where('tracking_number', '=', $paymentGateway->tracking_number)->orWhere('id', $paymentGateway->tracking_number)->first();
        $this->webhookSuccessResponse($order, $orderStatus, $paymentStatus);
    }

    /**
     * It creates a payment request and returns the response
     * 
     * @param array data 
     * 
     * @return PayWithIyzicoInitialize
     */
    public function requestToIyzico(array $data): PayWithIyzicoInitialize
    {
        extract($data);
        $redirectUrl = config('shop.shop_url');
        $order = Order::where('tracking_number', $order_tracking_number)->OrWhere('id', $order_tracking_number)->first();

        /* Creating a new instance of the class `CreatePayWithIyzicoInitializeRequest` */
        $request = new CreatePayWithIyzicoInitializeRequest();
        $request->setLocale(Locale::EN);
        $request->setPrice($amount);
        $request->setPaidPrice($amount);
        $request->setCurrency($this->currency ?? Currency::USD);
        $request->setCallbackUrl("{$redirectUrl}/orders/{$order_tracking_number}/thank-you");

        $buyer = new Buyer();
        $uuid = Str::uuid();
        $buyer->setId($uuid);
        $buyer->setName($order->customer_name);
        $buyer->setSurname($order->customer_name);
        $buyer->setEmail($order->customer->email ?? 'customer@demo.com');
        $buyer->setIdentityNumber($order_tracking_number);
        $buyer->setRegistrationAddress(json_encode($order->billing_address));
        $buyer->setIp($ip);
        $buyer->setCity($order->billing_address['city']);
        $buyer->setCountry($order->billing_address['country']);

        /* Setting the buyer information. */
        $request->setBuyer($buyer);

        $shippingAddress = new IyzicoAddress();
        $shippingAddress->setContactName($order->customer_name ?? "Jane Doe");
        $shippingAddress->setCity($order->shipping_address['city']);
        $shippingAddress->setCountry($order->shipping_address['country']);
        $shippingAddress->setAddress(json_encode($order->shipping_address));

        /* Setting the shipping address. */
        $request->setShippingAddress($shippingAddress);

        $billingAddress = new IyzicoAddress();
        $billingAddress->setContactName($order->customer_name ?? "Jane Doe");
        $billingAddress->setCity($order->billing_address['city']);
        $billingAddress->setCountry($order->billing_address['country']);
        $billingAddress->setAddress(json_encode($order->billing_address));

        /* Setting the billing address. */
        $request->setBillingAddress($billingAddress);

        /* Creating a new instance of the class `BasketItem` and setting the values of the properties. */
        $basketItems = Arr::map(collect($order->products)->toArray(), function ($product) {
            $product = (object) $product;
            $type = $product->is_digital ? BasketItemType::VIRTUAL : BasketItemType::PHYSICAL;
            $basketItem = new BasketItem();
            $basketItem->setId($product->id);
            $basketItem->setName($product->name);
            $basketItem->setCategory1($product->product_type);
            $basketItem->setItemType($type);
            if ($product->sale_price < 1) {
                $product->sale_price = 1;
            }
            $basketItem->setPrice(ceil($product->sale_price));
            return $basketItem;
        });
        $restPrice = $order->total - array_reduce($basketItems, fn ($price, $basketItem) => $price + $basketItem->getPrice(), 0);
        $basketItems[0]->setPrice($basketItems[0]->getPrice() + $restPrice);

        /* Setting the basket items. */
        $request->setBasketItems($basketItems);
        /* Creating a payment request. */
        $payWithIyzicoInitialize = PayWithIyzicoInitialize::create($request, $this->iyzicoClient);
        return $payWithIyzicoInitialize;
    }
}
