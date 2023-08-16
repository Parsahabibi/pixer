<?php

namespace Marvel\Payments;

use BitPaySDK\Client;
use BitPaySDK\Model\Invoice\Buyer;
use BitPaySDK\Model\Invoice\Invoice;
use Exception;
use Marvel\Database\Models\Order;
use Marvel\Enums\OrderStatus;
use Marvel\Enums\PaymentStatus;
use Marvel\Traits\PaymentTrait;
use Razorpay\Api\Errors\SignatureVerificationError;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Str;
use Marvel\Database\Models\PaymentIntent;
use Throwable;

class Bitpay extends Base implements PaymentInterface
{
    use PaymentTrait;

    protected  $bitPayClient;

    /**
     * @throws Throwable
     */
    public function __construct()
    {
        parent::__construct();

        // Initialize paypal client
        $this->bitPayClient = Client::create()->withFile(FULL_PATH_TO_THE_CONFIG_FILE);
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
            $invoice = $this->getInvoiceObject($data);
            $order = $this->bitPayClient->createInvoice($invoice);
            return [
                'redirect_url' => $order->getUrl(),
                'payment_id' => $order->getId(),
                'is_redirect' => true
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
        try {
            $result = $this->bitPayClient->getInvoice($id);
            return $result->getStatus() ? $result->getStatus() : false;
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
        // Verify webhook
        $data = $request->all();
        try {
            $paymentIntent = PaymentIntent::where('tracking_number', $data['data']['orderId'])->orWhere('order_id', $data['data']['orderId'])->firstOrFail();
        } catch (Exception $e) {
            http_response_code(400);
            exit();
        }

        switch (strtolower($data['data']['status'])) {
            case "complete":
                $this->updatePaymentOrderStatus($request, OrderStatus::COMPLETED, PaymentStatus::SUCCESS);
                break;
            case "confirmed":
                $this->updatePaymentOrderStatus($request, OrderStatus::COMPLETED, PaymentStatus::SUCCESS);
                break;
            case "paid":
                $this->updatePaymentOrderStatus($request, OrderStatus::PENDING, PaymentStatus::AWAITING_FOR_APPROVAL);
                break;
            case "new":
                $this->updatePaymentOrderStatus($request, OrderStatus::PENDING, PaymentStatus::PROCESSING);
                break;
            case "expired":
                $this->updatePaymentOrderStatus($request, OrderStatus::PENDING, PaymentStatus::FAILED);
                break;
            case "invalid":
                $this->updatePaymentOrderStatus($request, OrderStatus::PENDING, PaymentStatus::FAILED);
                break;
            default:
                $this->updatePaymentOrderStatus($request, OrderStatus::PENDING, PaymentStatus::FAILED);
                break;
        }

        // To prevent loop for any case
        http_response_code(200);
        exit();
    }

    /**
     * The function creates and returns an invoice object with various properties and settings for
     * payment processing.
     * 
     * @param array $data 
     * 
     * @return Invoice $invoice
     */
    private function getInvoiceObject(array $data): Invoice
    {
        extract($data);
        $redirectUrl = config('shop.shop_url');
        $authUser = auth()->check() ? auth()->user() : null;

        $invoice = new Invoice($amount, $this->currency);
        $invoice->setOrderId($order_tracking_number);
        $invoice->setFullNotifications(true);
        $invoice->setAutoRedirect(true);
        /**
         * Allows merchants to get access to additional webhooks. 
         * For instance when an invoice expires without receiving a payment or when it is refunded. If set to true, 
         * then fullNotifications is automatically set to true. 
         * When using the extendedNotifications parameter, 
         * the webhook also have a payload slightly different from the standard webhooks.
         * 
         */
        $invoice->setExtendedNotifications(true);
        $invoice->setNotificationURL(url('webhooks/bitpay'));
        $invoice->setRedirectURL("{$redirectUrl}/orders/{$order_tracking_number}/thank-you");
        $invoice->setCloseURL("{$redirectUrl}/orders/{$order_tracking_number}/thank-you");
        $invoice->setNotificationEmail($authUser?->email ?? '');
        $invoice->setPhysical(false);
        // $

        $buyer = new Buyer();
        $buyer->setName($authUser?->name ?? "Bily Matthews");
        $buyer->setEmail($authUser?->email ?? "");
        $buyer->setPhone($authUser?->profile?->contact ?? "+990123456789");
        $invoice->setBuyer($buyer);
        return $invoice;
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
        $trackingId = $request['data']['orderId'];
        $order = Order::where('tracking_number', '=', $trackingId)->orWhere('id', $trackingId)->firstOrFail();
        $this->webhookSuccessResponse($order, $orderStatus, $paymentStatus);
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
}
