<?php

namespace Marvel\Http\Controllers;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Marvel\Traits\WalletsTrait;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Marvel\Enums\Permission;
use Marvel\Exports\OrderExport;
use Illuminate\Http\JsonResponse;
use Marvel\Database\Models\Order;
use Maatwebsite\Excel\Facades\Excel;
use Marvel\Database\Models\Settings;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Marvel\Database\Repositories\OrderRepository;
use Marvel\Exceptions\MarvelException;
use Marvel\Database\Models\DownloadToken;
use Marvel\Http\Requests\OrderCreateRequest;
use Marvel\Http\Requests\OrderUpdateRequest;
use niklasravnsborg\LaravelPdf\Facades\Pdf as PDF;
use Marvel\Enums\PaymentGatewayType;
use Marvel\Traits\OrderManagementTrait;
use Marvel\Traits\PaymentStatusManagerWithOrderTrait;
use Marvel\Traits\PaymentTrait;
use Marvel\Traits\TranslationTrait;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrderController extends CoreController
{
    use WalletsTrait,
        OrderManagementTrait,
        TranslationTrait,
        PaymentStatusManagerWithOrderTrait,
        PaymentTrait;

    public OrderRepository $repository;
    public Settings $settings;

    public function __construct(OrderRepository $repository)
    {
        $this->repository = $repository;
        $this->settings = Settings::first();
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Collection|Order[]
     */
    public function index(Request $request)
    {
        $limit = $request->limit ? $request->limit : 10;
        return $this->fetchOrders($request)->paginate($limit)->withQueryString();
    }

    /**
     * fetchOrders
     *
     * @param  mixed $request
     * @return object
     */
    public function fetchOrders(Request $request)
    {
        $user = $request->user();

        if ($user && $user->hasPermissionTo(Permission::SUPER_ADMIN) && (!isset($request->shop_id) || $request->shop_id === 'undefined')) {
            return $this->repository->with('children')->where('id', '!=', null)->where('parent_id', '=', null); //->paginate($limit);
        } else if ($this->repository->hasPermission($user, $request->shop_id)) {
            // if ($user && $user->hasPermissionTo(Permission::STORE_OWNER)) {
            return $this->repository->with('children')->where('shop_id', '=', $request->shop_id)->where('parent_id', '!=', null); //->paginate($limit);
            // } elseif ($user && $user->hasPermissionTo(Permission::STAFF)) {
            //     return $this->repository->with('children')->where('shop_id', '=', $request->shop_id)->where('parent_id', '!=', null); //->paginate($limit);
            // }
        } else {
            return $this->repository->with('children')->where('customer_id', '=', $user->id)->where('parent_id', '=', null); //->paginate($limit);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  OrderCreateRequest  $request
     * @return LengthAwarePaginator|\Illuminate\Support\Collection|mixed
     * @throws MarvelException
     */
    public function store(OrderCreateRequest $request)
    {
        try {
            return DB::transaction(fn () => $this->repository->storeOrder($request, $this->settings));
        } catch (MarvelException $th) {
            throw new MarvelException(SOMETHING_WENT_WRONG, $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  Request  $request
     * @param $params
     * @return JsonResponse
     * @throws MarvelException
     */
    public function show(Request $request, $params)
    {
        $request["tracking_number"] = $params;
        try {
            return $this->fetchSingleOrder($request);
        } catch (MarvelException $e) {
            throw new MarvelException($e->getMessage());
        }
    }

    /**
     * fetchSingleOrder
     *
     * @param mixed $request
     * @return void
     * @throws MarvelException
     */
    public function fetchSingleOrder(Request $request)
    {
        $user = $request->user() ?? null;
        $language = $request->language ?? DEFAULT_LANGUAGE;
        $orderParam = $request->tracking_number ?? $request->id;
        try {
            $order = $this->repository->where('language', $language)->with([
                'products',
                'children.shop',
                'wallet_point',
            ])->where('id', $orderParam)->orWhere('tracking_number', $orderParam)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException(NOT_FOUND);
        }

        // Create Intent
        if (
            !in_array($order->payment_gateway, [
                PaymentGatewayType::CASH, PaymentGatewayType::CASH_ON_DELIVERY, PaymentGatewayType::FULL_WALLET_PAYMENT
            ])
        ) {
            $order['payment_intent'] = $this->processPaymentIntent($request, $this->settings);
        }

        if (!$order->customer_id) {
            return $order;
        }
        if ($user && $user->hasPermissionTo(Permission::SUPER_ADMIN)) {
            return $order;
        } elseif (isset($order->shop_id)) {
            if ($user && ($this->repository->hasPermission($user, $order->shop_id) || $user->id == $order->customer_id)) {
                return $order;
            }
        } elseif ($user && $user->id == $order->customer_id) {
            return $order;
        } else {
            throw new AuthorizationException(NOT_AUTHORIZED);
        }
    }

    /**
     * findByTrackingNumber
     *
     * @param  mixed $request
     * @param  mixed $tracking_number
     * @return void
     */
    public function findByTrackingNumber(Request $request, $tracking_number)
    {
        $user = $request->user() ?? null;
        try {
            $order = $this->repository->with(['products', 'children.shop', 'wallet_point', 'payment_intent'])
                ->findOneByFieldOrFail('tracking_number', $tracking_number);

            if ($order->customer_id === null) {
                return $order;
            }
            if ($user && ($user->id === $order->customer_id || $user->can('super_admin'))) {
                return $order;
            } else {
                throw new AuthorizationException(NOT_AUTHORIZED);
            }
        } catch (MarvelException $e) {
            throw new MarvelException(NOT_FOUND);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param OrderUpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(OrderUpdateRequest $request, $id)
    {
        try {
            $request["id"] = $id;
            return $this->updateOrder($request);
        } catch (MarvelException $e) {
            throw new MarvelException(COULD_NOT_UPDATE_THE_RESOURCE, $e->getMessage());
        }
    }

    public function updateOrder(OrderUpdateRequest $request)
    {
        return $this->repository->updateOrder($request);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        try {
            return $this->repository->findOrFail($id)->delete();
        } catch (MarvelException $e) {
            throw new MarvelException(NOT_FOUND);
        }
    }

    /**
     * Export order dynamic url
     *
     * @param Request $request
     * @param int $shop_id
     * @return string
     */
    public function exportOrderUrl(Request $request, $shop_id = null)
    {
        try {
            $user = $request->user();

            if ($user && !$this->repository->hasPermission($user, $request->shop_id)) {
                throw new AuthorizationException(NOT_AUTHORIZED);
            }

            $dataArray = [
                'user_id' => $user->id,
                'token'   => Str::random(16),
                'payload' => $request->shop_id
            ];
            $newToken = DownloadToken::create($dataArray);

            return route('export_order.token', ['token' => $newToken->token]);
        } catch (MarvelException $e) {
            throw new MarvelException(SOMETHING_WENT_WRONG, $e->getMessage());
        }
    }

    /**
     * Export order to excel sheet
     *
     * @param string $token
     * @return void
     */
    public function exportOrder($token)
    {
        $shop_id = 0;
        try {
            $downloadToken = DownloadToken::where('token', $token)->first();

            $shop_id = $downloadToken->payload;
            $downloadToken->delete();
        } catch (MarvelException $e) {
            throw new MarvelException(TOKEN_NOT_FOUND);
        }

        try {
            return Excel::download(new OrderExport($this->repository, $shop_id), 'orders.xlsx');
        } catch (MarvelException $e) {
            throw new MarvelException(NOT_FOUND);
        }
    }

    /**
     * Export order dynamic url
     *
     * @param Request $request
     * @param int $shop_id
     * @return string
     */
    public function downloadInvoiceUrl(Request $request)
    {

        try {
            $user = $request->user();
            if ($user && !$this->repository->hasPermission($user, $request->shop_id)) {
                throw new AuthorizationException(NOT_AUTHORIZED);
            }
            if (empty($request->order_id)) {
                throw new NotFoundHttpException(NOT_FOUND);
            }
            $language = $request->language ?? DEFAULT_LANGUAGE;
            $isRTL = $request->is_rtl ?? false;

            $translatedText = $this->formatInvoiceTranslateText($request->translated_text);

            $payload = [
                'user_id'           => $user->id,
                'order_id'          => intval($request->order_id),
                'language'          => $language,
                'translated_text'   => $translatedText,
                'is_rtl'            => $isRTL
            ];

            $data = [
                'user_id' => $user->id,
                'token'   => Str::random(16),
                'payload' => serialize($payload)
            ];

            $newToken = DownloadToken::create($data);

            return route('download_invoice.token', ['token' => $newToken->token]);
        } catch (MarvelException $e) {
            throw new MarvelException($e->getMessage());
        }
    }

    /**
     * Export order to excel sheet
     *
     * @param string $token
     * @return void
     */
    public function downloadInvoice($token)
    {
        $payloads = [];
        try {
            $downloadToken = DownloadToken::where('token', $token)->firstOrFail();
            $payloads      = unserialize($downloadToken->payload);
            $downloadToken->delete();
        } catch (MarvelException $e) {
            throw new MarvelException(TOKEN_NOT_FOUND);
        }

        try {
            $settings = Settings::getData($payloads['language']);
            $order = $this->repository->with(['products', 'children.shop', 'wallet_point'])->where('id', $payloads['order_id'])->firstOrFail();
            $invoiceData = [
                'order'           => $order,
                'settings'        => $settings,
                'translated_text' => $payloads['translated_text'],
                'is_rtl'          => $payloads['is_rtl'],
                'language'        => $payloads['language'],
            ];

            $pdf = PDF::loadView('pdf.order-invoice', $invoiceData);
            $filename = 'invoice-order-' . $payloads['order_id'] . '.pdf';
            return $pdf->download($filename);
        } catch (MarvelException $e) {
            throw new MarvelException(NOT_FOUND);
        }
    }

    /**
     * submitPayment
     *
     * @param  mixed  $request
     * @return void
     * @throws Exception
     */
    public function submitPayment(Request $request): void
    {
        $tracking_number = $request->tracking_number ?? null;
        try {
            $order = $this->repository->with(['products', 'children.shop', 'wallet_point', 'payment_intent'])
                ->findOneByFieldOrFail('tracking_number', $tracking_number);

            switch ($order->payment_gateway) {

                case PaymentGatewayType::STRIPE:
                    $this->stripe($order, $request, $this->settings);
                    break;

                case PaymentGatewayType::PAYPAL:
                    $this->paypal($order, $request, $this->settings);
                    break;

                case PaymentGatewayType::MOLLIE:
                    $this->mollie($order, $request, $this->settings);
                    break;

                case PaymentGatewayType::RAZORPAY:
                    $this->razorpay($order, $request, $this->settings);
                    break;
                case PaymentGatewayType::SSLCOMMERZ:
                    $this->sslcommerz($order, $request, $this->settings);
                    break;
                case PaymentGatewayType::PAYSTACK:
                    $this->paystack($order, $request, $this->settings);
                    break;
                case PaymentGatewayType::XENDIT:
                    $this->xendit($order, $request, $this->settings);
                case PaymentGatewayType::IYZICO:
                    $this->iyzico($order, $request, $this->settings);
                    break;
                case PaymentGatewayType::COINBASE:
                    $this->coinbase($order, $request, $this->settings);
                    break;
                case PaymentGatewayType::BITPAY:
                    $this->bitpay($order, $request, $this->settings);
                    break;
            }
        } catch (MarvelException $e) {
            throw new MarvelException(SOMETHING_WENT_WRONG, $e->getMessage());
        }
    }
}
