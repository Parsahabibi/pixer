<?php


namespace Marvel\Http\Controllers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Marvel\Database\Models\Balance;
use Marvel\Database\Models\Withdraw;
use Marvel\Database\Repositories\WithdrawRepository;
use Marvel\Enums\Permission;
use Marvel\Enums\WithdrawStatus;
use Marvel\Exceptions\MarvelException;
use Marvel\Http\Requests\UpdateWithdrawRequest;
use Marvel\Http\Requests\WithdrawRequest;
use Prettus\Validator\Exceptions\ValidatorException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WithdrawController extends CoreController
{
    public $repository;

    public function __construct(WithdrawRepository $repository)
    {
        $this->repository = $repository;
    }
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Collection|Withdraw[]
     */
    public function index(Request $request)
    {
        $limit = $request->limit ?   $request->limit : 15;
        $withdraw = $this->fetchWithdraws($request);
        return $withdraw->paginate($limit);
    }

    public function fetchWithdraws(Request $request)
    {
        try {
            $user = $request->user();
            $shop_id = isset($request['shop_id']) && $request['shop_id'] != 'undefined' ? $request['shop_id'] : false;
            if ($shop_id) {
                if ($user->shops->contains('id', $shop_id)) {
                    return $this->repository->with(['shop'])->where('shop_id', '=', $shop_id);
                } elseif ($user && $user->hasPermissionTo(Permission::SUPER_ADMIN)) {
                    return $this->repository->with(['shop'])->where('shop_id', '=', $shop_id);
                } else {
                    throw new AuthorizationException(NOT_AUTHORIZED);
                }
            } else {
                if ($user && $user->hasPermissionTo(Permission::SUPER_ADMIN)) {
                    return $this->repository->with(['shop'])->where('id', '!=', null);
                } else {
                    throw new AuthorizationException(NOT_AUTHORIZED);
                }
            }
        } catch (MarvelException $e) {
            throw new MarvelException($e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param WithdrawRequest $request
     * @return mixed
     * @throws ValidatorException
     */
    public function store(WithdrawRequest $request)
    {
        try {
            if ($request->user() && ($request->user()->hasPermissionTo(Permission::SUPER_ADMIN) || $request->user()->shops->contains('id', $request->shop_id))) {
                $validatedData = $request->validated();
                if (!isset($validatedData['shop_id'])) {
                    throw new BadRequestHttpException(WITHDRAW_MUST_BE_ATTACHED_TO_SHOP);
                }
                $balance = Balance::where('shop_id', '=', $validatedData['shop_id'])->first();
                if (isset($balance->current_balance) && $balance->current_balance <= $validatedData['amount']) {
                    throw new BadRequestHttpException(INSUFFICIENT_BALANCE);
                }
                $withdraw = $this->repository->create($validatedData);
                $balance->withdrawn_amount = $balance->withdrawn_amount + $validatedData['amount'];
                $balance->current_balance = $balance->current_balance - $validatedData['amount'];
                $balance->save();
                $withdraw->status = WithdrawStatus::PENDING;
                return $withdraw;
            }
            throw new AuthorizationException(NOT_AUTHORIZED);
        } catch (MarvelException $e) {
            throw new MarvelException(SOMETHING_WENT_WRONG);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, $id)
    {
        $request->id = $id;
        return $this->fetchSingleWithdraw($request);
    }

    public function fetchSingleWithdraw(Request $request)
    {
        try {
            $id = $request->id;
            $withdraw = $this->repository->with(['shop'])->findOrFail($id);
            if ($request->user() && ($request->user()->hasPermissionTo(Permission::SUPER_ADMIN) || $request->user()->shops->contains('id', $withdraw->shop_id))) {
                return $withdraw;
            }
            throw new AuthorizationException(NOT_AUTHORIZED);
        } catch (MarvelException $e) {
            throw new MarvelException(SOMETHING_WENT_WRONG);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param WithdrawRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateWithdrawRequest $request, $id)
    {
        throw new HttpException(400, ACTION_NOT_VALID);
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
            if ($request->user() && $request->user()->hasPermissionTo(Permission::SUPER_ADMIN)) {
                return $this->repository->findOrFail($id)->delete();
            }
            throw new AuthorizationException(NOT_AUTHORIZED);
        } catch (MarvelException $e) {
            throw new MarvelException(COULD_NOT_DELETE_THE_RESOURCE);
        }
    }

    public function approveWithdraw(Request $request)
    {
        try {
            if ($request->user() && $request->user()->hasPermissionTo(Permission::SUPER_ADMIN)) {
                $id = $request->id;
                $status = $request->status->value ?? $request->status;
                $withdraw = $this->repository->findOrFail($id);
                $withdraw->status = $status;
                $withdraw->save();
                return $withdraw;
            }
            throw new AuthorizationException(NOT_AUTHORIZED);
        } catch (MarvelException $e) {
            throw new MarvelException(SOMETHING_WENT_WRONG);
        }
    }
}
