<?php

namespace Marvel\Payments;

use Pishran\Zarinpal\Zarinpal as ZarinpalPackage;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Exception;

class Zarinpal extends Base implements PaymentInterface
{
    protected ZarinpalPackage $zarinpal;

    public function __construct()
    {
        parent::__construct();

        // Initialize Zarinpal package with your merchant ID
        $this->zarinpal = new ZarinpalPackage([
            'merchant_id' => config('zarinpal.merchant_id'),
        ]);
    }

    public function getIntent(array $data): array
    {
        try {
            extract($data);
            $response = $this->zarinpal->amount($amount)
                ->request()
                ->description($description)
                ->callbackUrl($callback_url)
                ->mobile($mobile)
                ->email($email)
                ->send();

            if (!$response->success()) {
                return ['success' => false, 'message' => $response->error()->message()];
            }

            $authority = $response->authority();

            return [
                'success' => true,
                'redirect_url' => $response->redirect(),
                'authority' => $authority,
            ];
        } catch (Exception $e) {
            throw new HttpException(400, SOMETHING_WENT_WRONG_WITH_PAYMENT);
        }
    }

    public function verify(string $id): mixed
    {
        try {
            $response = $this->zarinpal->verification()
                ->authority($id)
                ->send();

            if (!$response->success()) {
                return ['success' => false, 'message' => $response->error()->message()];
            }

            $cardHash = $response->cardHash();
            $cardPan = $response->cardPan();

            return ['success' => true, 'cardHash' => $cardHash, 'cardPan' => $cardPan];
        } catch (Exception $e) {
            throw new HttpException(400, SOMETHING_WENT_WRONG_WITH_PAYMENT);
        }
    }

    public function handleWebHooks(object $request): void
    {
        // Implement based on your needs
    }

    public function createCustomer(object $request): array
    {
        // Implement based on your needs
        return [];
    }

    public function attachPaymentMethodToCustomer(string $retrieved_payment_method, object $request): object
    {
        // Implement based on your needs
        return (object) [];
    }

    public function detachPaymentMethodToCustomer(string $retrieved_payment_method): object
    {
        // Implement based on your needs
        return (object) [];
    }

    public function retrievePaymentIntent(string $payment_intent_id): object
    {
        // Implement based on your needs
        return (object) [];
    }

    public function confirmPaymentIntent(string $payment_intent_id, array $data): object
    {
        // Implement based on your needs
        return (object) [];
    }

    public function setIntent(array $data): array
    {
        // Implement based on your needs
        return [];
    }

    public function retrievePaymentMethod(string $method_key): object
    {
        // Implement based on your needs
        return (object) [];
    }
}
