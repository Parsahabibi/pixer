<?php

namespace Marvel\Payments;


class Payment
{
  public $payment;

  public function __construct(PaymentInterface $payment)
  {
    $this->payment = $payment;
  }

  /**
   * createCustomer
   *
   * @param  object $request
   * @return array
   */
  public function createCustomer($request)
  {
    return $this->payment->createCustomer($request);
  }


  /**
   * attachPaymentMethodToCustomer
   *
   * @param  string $retrieved_payment_method
   * @param  object $request
   * @return object
   */
  public function attachPaymentMethodToCustomer($retrieved_payment_method, $request)
  {
    return $this->payment->attachPaymentMethodToCustomer($retrieved_payment_method, $request);
  }


  /**
   * detachPaymentMethodToCustomer
   *
   * @param  string $retrieved_payment_method
   * @return object
   */
  public function detachPaymentMethodToCustomer($retrieved_payment_method)
  {
    return $this->payment->detachPaymentMethodToCustomer($retrieved_payment_method);
  }

  /**
   * getIntent
   *
   * @param  array $data
   * @return array
   */
  public function getIntent($data)
  {
    return $this->payment->getIntent($data);
  }

  /**
   * retrievePaymentIntent
   *
   * @param  string $payment_intent_id
   * @return object
   */
  public function retrievePaymentIntent($payment_intent_id)
  {
    return $this->payment->retrievePaymentIntent($payment_intent_id);
  }

  /**
   * confirmPaymentIntent
   *
   * @param  string $payment_intent_id
   * @param  array $data
   * @return object
   */
  public function confirmPaymentIntent($payment_intent_id, $data)
  {
    return $this->payment->confirmPaymentIntent($payment_intent_id, $data);
  }

    /**
     * verify
     *
     * @param $id
     * @return bool
     */
  public function verify($id): mixed
  {
    return $this->payment->verify($id);
  }

  /**
   * handleWebHooks
   *
   * @param  object $request
   * @return void
   */
  public function handleWebHooks($request)
  {
    $this->payment->handleWebHooks($request);
  }

  /**
   * setIntent
   *
   * @param  array $data
   * @return array
   */
  public function setIntent($data)
  {
    return $this->payment->setIntent($data);
  }

  /**
   * retrievePaymentMethod
   *
   * @param  string $method_key
   * @return object
   */
  public function retrievePaymentMethod($method_key)
  {
    return $this->payment->retrievePaymentMethod($method_key);
  }
}
