<?php

namespace Marvel\Payments;

interface PaymentInterface
{
  public function getIntent(array $data): array;

  public function verify(string $id): mixed;

  public function handleWebHooks(object $request): void;

  public function createCustomer(object $request): array;

  public function attachPaymentMethodToCustomer(string $retrieved_payment_method, object $request): object;

  public function detachPaymentMethodToCustomer(string $retrieved_payment_method): object;

  public function retrievePaymentIntent(string $payment_intent_id): object;

  public function confirmPaymentIntent(string $payment_intent_id, array $data): object;

  public function setIntent(array $data): array;

  public function retrievePaymentMethod(string $method_key): object;
}
