<?php

namespace Marvel\Payments;

use Exception;
use Illuminate\Support\Facades\Http;
use Marvel\Exceptions\MarvelException;
use Marvel\Payments\PaymentInterface;
use Marvel\Enums\OrderStatus;
use Marvel\Database\Models\Order;
use Marvel\Enums\PaymentStatus;
use Marvel\Traits\PaymentTrait;
use Marvel\Payments\Base;
use Xendit\Invoice;
use Xendit\Xendit as XenditFacade;


class Xendit extends Base implements PaymentInterface
{

  use PaymentTrait;
  public XenditFacade $xenditClient;

  public function __construct()
  {
    parent::__construct();
    $this->xenditClient = new XenditFacade();
    $this->xenditClient->setApiKey(config('shop.xendit.api_key'));
  }

  public function getIntent($data): array
  {
    try {
      extract($data);
      $params = [
        'currency' => $this->currency,
        'amount'   => round($amount, 2),
        'callback_url' => config("shop.xendit.webhook_url"),
        "description" => "Order From " . $order_tracking_number,
        'success_redirect_url' => config("shop.shop_url") . "/orders/{$order_tracking_number}/thank-you",
        'failure_redirect_url' => config("shop.shop_url") . "/orders/{$order_tracking_number}/payment",
        'external_id' => $order_tracking_number,
      ];

      $order = Invoice::create($params);
      return [
        'order_tracking_number'   => $order_tracking_number,
        'is_redirect'  => true,
        'payment_id'   => $order['id'],
        'redirect_url' => $order['invoice_url'],
      ];
    } catch (Exception $e) {
      throw new MarvelException(SOMETHING_WENT_WRONG_WITH_PAYMENT);
    }
  }


  public function verify($paymentId): mixed
  {
    try {
      $result = Invoice::retrieve($paymentId);
      return isset($result['status']) ? $result['status'] : false;
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
    // Verify webhook
    $xenditXCallbackToken = config('shop.xendit.xendit_callback_token');
    $reqHeaders = getallheaders();
    $xIncomingCallbackTokenHeader = isset($reqHeaders['X-Callback-Token']) ? $reqHeaders['X-Callback-Token'] : "";
    
    if ($xIncomingCallbackTokenHeader === $xenditXCallbackToken) {
      $rawRequestInput = file_get_contents("php://input");
      $arrRequestInput = json_decode($rawRequestInput, true);
    } else {
      http_response_code(403);
    }

    switch (strtolower($arrRequestInput['status'])) {
      case "paid":
        $this->updatePaymentOrderStatus($request, OrderStatus::PROCESSING, PaymentStatus::SUCCESS);
        break;
      case "pending":
        $this->updatePaymentOrderStatus($request, OrderStatus::PENDING, PaymentStatus::PENDING);
        break;
      case "failed":
        $this->updatePaymentOrderStatus($request, OrderStatus::FAILED, PaymentStatus::FAILED);
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

    $trackingId = $request['external_id'];
    $order = Order::where('tracking_number', '=', $trackingId)->first();
    $this->webhookSuccessResponse($order, $orderStatus, $paymentStatus);
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
  public function attachPaymentMethodToCustomer(string $retrieved_payment_method, object $request): object
  {
    return (object) [];
  }

  /**
   * detachPaymentMethodToCustomer
   *
   * @param  string  $retrieved_payment_method
   * @return object
   */
  public function detachPaymentMethodToCustomer(string $retrieved_payment_method): object
  {
    return (object) [];
  }


  public function retrievePaymentIntent($payment_intent_id): object
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
  public function confirmPaymentIntent(string $payment_intent_id, array $data): object
  {
    return (object) [];
  }

  /**
   * setIntent
   *
   * @param  array  $data
   * @return array
   */
  public function setIntent(array $data): array
  {
    return [];
  }

  /**
   * retrievePaymentMethod
   *
   * @param  string  $method_key
   * @return object
   */
  public function retrievePaymentMethod(string $method_key): object
  {
    return (object) [];
  }
}
