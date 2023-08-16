<?php

namespace Marvel\Http\Controllers;

use App\Events\QuestionAnswered;
use App\Events\RefundApproved;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Marvel\Database\Models\Balance;
use Marvel\Database\Models\Order;
use Marvel\Database\Models\Wallet;
use Marvel\Database\Repositories\RefundRepository;
use Marvel\Enums\Permission;
use Marvel\Enums\RefundStatus;
use Marvel\Exceptions\MarvelException;
use Marvel\Http\Requests\RefundRequest;
use Marvel\Traits\WalletsTrait;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RefundController extends CoreController
{
    use WalletsTrait;

    public $repository;

    public function __construct(RefundRepository $repository)
    {
        $this->repository = $repository;
    }


    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Collection|Type[]
     */
    public function index(Request $request)
    {
        $limit = $request->limit;
        return $this->fetchRefunds($request)->paginate($limit)->withQueryString();
    }

    /**
     * Fetch refunds
     */
    // public function fetchRefunds(Request $request)
    // {
    //     $language = $request->language ?? DEFAULT_LANGUAGE;
    //     $user = $request->user();

    //     $orderQuery = $this->repository->whereHas('order', function ($q) use ($language) {
    //         $q->where('language', $language);
    //     })->with(['order', 'shop', 'customer']);

    //     if ($user && $user->hasPermissionTo(Permission::SUPER_ADMIN) && (!isset($request->shop_id) || $request->shop_id === 'undefined')) {
    //         return $orderQuery->where('id', '!=', null)->where('shop_id', '=', null);
    //     } else if ($this->repository->hasPermission($user, $request->shop_id)) {
    //         return $orderQuery->where('shop_id', '=', $request->shop_id);
    //     } else if ($user && $user->hasPermissionTo(Permission::CUSTOMER)) {
    //         return $orderQuery->where('customer_id', $user->id)->where('shop_id', null);
    //     }
    //     throw new MarvelException(NOT_AUTHORIZED);
    // }

    public function fetchRefunds(Request $request)
    {
        try {
            $user = $request->user();
            if ($user && $user->hasPermissionTo(Permission::SUPER_ADMIN) && (!isset($request->shop_id) || $request->shop_id === 'undefined')) {
                return $this->repository->with(['order', 'shop', 'customer'])->where('id', '!=', null)->where('shop_id', '=', null);
            } else if ($this->repository->hasPermission($user, $request->shop_id)) {
                return $this->repository->with(['order', 'shop', 'customer'])->where('shop_id', '=', $request->shop_id);
            } else if ($user && $user->hasPermissionTo(Permission::CUSTOMER)) {
                return $this->repository->with(['order', 'shop', 'customer'])->where('customer_id', $user->id)->where('shop_id', null);
            }
            throw new AuthorizationException(NOT_AUTHORIZED);
        } catch (MarvelException $th) {
            throw new MarvelException(SOMETHING_WENT_WRONG);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param RefundRequest $request
     * @return mixed
     * @throws ValidatorException
     */
    public function store(RefundRequest $request)
    {
        try {
            if (!$request->user()) {
                throw new AuthorizationException(NOT_AUTHORIZED);
            }
            return $this->repository->storeRefund($request);
        } catch (MarvelException $th) {
            throw new MarvelException(COULD_NOT_CREATE_THE_RESOURCE);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return JsonResponse
     */
    public function show($id)
    {
        try {
            return $this->repository->with(['shop', 'order', 'customer'])->findOrFail($id);
        } catch (MarvelException $e) {
            throw new MarvelException(NOT_FOUND);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request  $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $request->id = $id;
            return $this->updateRefund($request);
        } catch (MarvelException $th) {
            throw new MarvelException(COULD_NOT_UPDATE_THE_RESOURCE);
        }
    }

    public function updateRefund(Request $request)
    {
        $user = $request->user();

        if ($this->repository->hasPermission($user)) {
            try {
                $refund = $this->repository->with(['shop', 'order', 'customer'])->findOrFail($request->id);
            } catch (\Exception $e) {
                throw new ModelNotFoundException(NOT_FOUND);
            }
            if ($refund->status == RefundStatus::APPROVED) {
                throw new HttpException(400, ALREADY_REFUNDED);
            }
            $this->repository->updateRefund($request, $refund);
            if ($request->status == RefundStatus::APPROVED) {
                try {
                    $order = Order::findOrFail($refund->order_id);
                    foreach ($order->children as $childOrder) {
                        $balance = Balance::where('shop_id', $childOrder->shop_id)->first();
                        $balance->total_earnings = $balance->total_earnings - $childOrder->amount;
                        $balance->current_balance = $balance->current_balance - $childOrder->amount;
                        $balance->save();
                    }
                } catch (Exception $e) {
                    throw new ModelNotFoundException(NOT_FOUND);
                }
                $wallet = Wallet::firstOrCreate(['customer_id' => $refund->customer_id]);
                $walletPoints = $this->currencyToWalletPoints($refund->amount);
                $wallet->total_points = $wallet->total_points + $walletPoints;
                $wallet->available_points = $wallet->available_points + $walletPoints;
                $wallet->save();

                // refund approved event
                event(new RefundApproved($refund));
            }
            return $refund;
        } else {
            throw new AuthorizationException(NOT_AUTHORIZED);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        try {
            $request->id = $id;
            return $this->deleteRefund($request);
        } catch (MarvelException $th) {
            throw new MarvelException(COULD_NOT_DELETE_THE_RESOURCE);
        }
    }

    public function deleteRefund(Request $request)
    {
        try {
            $refund = $this->repository->findOrFail($request->id);
        } catch (\Exception $e) {
            throw new ModelNotFoundException(NOT_FOUND);
        }
        if ($this->repository->hasPermission($request->user())) {
            $refund->delete();
            return $refund;
        } else {
            throw new AuthorizationException(NOT_AUTHORIZED);
        }
    }
}
