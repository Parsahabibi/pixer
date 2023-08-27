<?php

namespace Marvel\Payments;

use Marvel\Enums\OrderStatus;
use Marvel\Enums\PaymentStatus;
use Marvel\Exceptions\MarvelException;
use Marvel\Traits\PaymentTrait;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;
use Shetabit\Multipay\Receipt;
use Shetabit\Payment\Facade\Payment as ZibalPayment;

use Shetabit\Multipay\Invoice;
use Throwable;

class Zibal extends Base implements PaymentInterface
{
    use PaymentTrait;

    protected Invoice $invoice;

    /**
     * @throws Throwable
     */
    public function __construct()
    {
        parent::__construct();

        $this->invoice = new Invoice();

    }

    public function getIntent(array $data): array
    {

        try {
            extract($data);
            $order = $this->invoice;
            $order->amount($amount);
            $order->uuid();
            $order->transactionId($order->getUuid());
            $order_array = ZibalPayment::purchase(
                $order,
                function ($driver, $transactionId) {
                }
            )->pay()->toJson();
            return [
                'order_tracking_number' => $order_tracking_number,
                'is_redirect' => true,
                'payment_id' => $order->getTransactionId(),
                'redirect_url' => 'https://gateway.zibal.ir/start/' . $order->getTransactionId(),
            ];
        } catch (Exception $e) {
            throw new MarvelException(SOMETHING_WENT_WRONG_WITH_PAYMENT);
        }
    }

    public function verify(string $id): mixed
    {
        try {

            $verifyPayment = $this->settings->verifyApiUrl;
            $verifyUrl = $verifyPayment . $this->invoice->getTransactionId();
            throw new InvalidPaymentException('a suitable message');

        } catch (Exception $e) {
            throw new MarvelException(SOMETHING_WENT_WRONG_WITH_PAYMENT);
        }
    }

    public function handleWebHooks(object $request): void
    {
//        try {
//            // Get the transaction ID from the webhook payload
//            $transactionId = $request->input('transactionId');
//
//            $receipt = $this->findReceipt($transactionId);
//
//            // Verify the payment status with Zibal API or other means
//            $isPaymentSuccessful = $this->verifyPayment($receipt);
//
//            if ($isPaymentSuccessful) {
//                // Update the payment and order status accordingly
//                $orderTrackingNumber = $receipt->getExtra('order_tracking_number');
//                $order = Order::where('tracking_number', $orderTrackingNumber)->first();
//
//                if ($order) {
//                    $this->updatePaymentOrderStatus($order, OrderStatus::PROCESSING, PaymentStatus::SUCCESS);
//                }
//            }
//        } catch (ReceiptNotFoundException $e) {
//            // Handle receipt not found
//        } catch (InvalidPaymentException $e) {
//            // Handle invalid payment status or other issues
//        } catch (Exception $e) {
//            // Handle other exceptions
//        }
//
//        // Respond to the webhook
//        http_response_code(200);
//        exit();

    }

    public function createCustomer(object $request): array
    {
        // Implement logic to create a customer in Zibal
        return [];
    }

    public function attachPaymentMethodToCustomer(string $retrieved_payment_method, object $request): object
    {
        // Implement logic to attach a payment method to a customer in Zibal
        return (object)[];
    }

    public function detachPaymentMethodToCustomer(string $retrieved_payment_method): object
    {
        // Implement logic to detach a payment method from a customer in Zibal
        return (object)[];
    }

    public function retrievePaymentIntent(string $payment_intent_id): object
    {
        // Implement logic to retrieve payment intent in Zibal
        return (object)[];
    }

    public function confirmPaymentIntent(string $payment_intent_id, array $data): object
    {
        // Implement logic to confirm payment intent in Zibal
        return (object)[];
    }

    public function setIntent(array $data): array
    {
        // Implement logic to set intent in Zibal
        return [];
    }

    public function retrievePaymentMethod(string $method_key): object
    {
        // Implement logic to retrieve payment method in Zibal
        return (object)[];
    }

    // Additional methods specific to Zibal gateway
}
