<?php


namespace Marvel\Database\Repositories;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Marvel\Database\Models\Shop;
use Marvel\Database\Models\StoreNotice;
use Marvel\Database\Models\User;
use Marvel\Enums\Permission;
use Marvel\Enums\StoreNoticeType;
use Marvel\Events\StoreNoticeEvent;
use Marvel\Exceptions\MarvelException;
use Marvel\Traits\StoreNoticeable;
use Mpdf\Container\NotFoundException;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class StoreNoticeRepository extends BaseRepository
{
    use StoreNoticeable;

    /**
     * @var array
     */
    protected $fieldSearchable = [
        'notice'       => 'like',
        'effective_from',
        'expired_at',
        'receiver.id',
        'creator_role' => 'like',
    ];

    /**
     * @var array
     */
    protected $dataArray = [
        'priority',
        'notice',
        'description',
        'effective_from',
        'expired_at',
        'type',
    ];


    public function boot()
    {
        try {
            $this->pushCriteria(app(RequestCriteria::class));
        } catch (RepositoryException $e) {
            //
        }
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return StoreNotice::class;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws MarvelException
     */
    public function fetchStoreNotices(Request $request): mixed
    {

        try {

            $storeNotices = $this->where('id', '!=', null);

            /* for Guest user Requesting from shop */

            if (!$request->user()) {
                $shop_id = $request['shop_id'] ?? 0;
                $shop = Shop::where('id', $shop_id)->orWhere('slug', $shop_id)->first();
                if (!$shop) {
                    throw new NotFoundException(NOT_FOUND);
                }
                return $storeNotices
                    ->where([
                        'created_by' => $shop->owner_id ?? 0,
                    ])->whereRelation('shops', 'id', $shop_id)
                    ->whereDate('expired_at', '>=', now());
            }

            if (!$request->user()->hasPermissionTo(Permission::SUPER_ADMIN)) {

                /* Block for authenticated user [vendor or staff] */
                if (!empty($request['shop_id'])) {
                    $shop_id = $request['shop_id'];
                    $shop = Shop::findOrFail($shop_id);
                    $storeNotices
                        ->where([
                            'created_by' => $shop->owner_id ?? 0,
                        ])->whereRelation('shops', 'id', $shop_id);
                } elseif ($request->user()->managed_shop) {
                    /* Block for staff notices */
                    $shop_id = $request->user()->managed_shop->id ?? 0;
                    $storeNotices
                        ->where([
                            'created_by' => $request->user()->managed_shop->owner_id ?? 0,
                        ])->whereRelation('shops', 'id', $shop_id);
                } else {
                    /* Block for Store owner notices */
                    $storeNotices->where('created_by', $request->user()->id)
                        ->orWhereRelation('users', 'id', $request->user()->id);
                }
            }
            if(isset($request['shop_id'])){
                $storeNotices->whereRelation('shops', 'id', $request['shop_id']);
            }
            return $storeNotices->whereDate('expired_at', '>=', now());
        } catch (Exception $e) {
            throw new Exception(SOMETHING_WENT_WRONG);
        }
    }

    /**
     * @param Request $request
     * @return array[]
     */
    public function fetchStoreNoticeType(Request $request)
    {
        if ($request->user()->hasPermissionTo(Permission::SUPER_ADMIN)) {
            $typeArr = [
                ['name' => "ALL VENDOR", 'value' => StoreNoticeType::ALL_VENDOR],
                ['name' => "SPECIFIC VENDOR", 'value' => StoreNoticeType::SPECIFIC_VENDOR]
            ];
            return $typeArr;
        }
        $typeArr = [
            ['name' => "ALL SHOP", 'value' => StoreNoticeType::ALL_SHOP],
            ['name' => "SPECIFIC SHOP", 'value' => StoreNoticeType::SPECIFIC_SHOP]
        ];
        return $typeArr;
    }

    /**
     * This method will generate User list or Shop list based on requested user permission
     * @param Request $request
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Http\Response
     * @throws MarvelException
     */
    public function fetchUserToSendNotification(Request $request)
    {
        try {
            if ($request->user()->hasPermissionTo(Permission::SUPER_ADMIN)) {
                return User::with('permissions')->whereHas('permissions', function (Builder $builder) {
                    $builder->whereNotIn('name', [Permission::SUPER_ADMIN, Permission::CUSTOMER]);
                })->orderBy('name')->get();
            } else {
                return $request->user()->shops->where('is_active', 1);
            }
        } catch (Exception $e) {
            throw new Exception(SOMETHING_WENT_WRONG);
        }
    }


    /**
     * It creates a new store notice, syncs the users and shops, and syncs the read status.
     *
     * @param Request request The request object
     *
     * @return StoreNotice storeNotice is being returned.
     */
    public function saveStoreNotice(Request $request)
    {
        try {
            $storeNotice = $this->create($request->only($this->dataArray));
            $this->syncUsersOrShops($request, $storeNotice);
            $this->syncReadStatus($storeNotice);
            event(new StoreNoticeEvent($storeNotice, 'create'));
            return $storeNotice;
        } catch (Exception $e) {
            throw new HttpException(400, COULD_NOT_CREATE_THE_RESOURCE);
        }
    }

    /**
     * Updating Specific resource in storage
     *
     * @param \Marvel\Database\Models\StoreNotice $storeNotice
     * @param array $data
     * @return mixed
     */
    public function updateStoreNotice(Request $request, StoreNotice $storeNotice)
    {

        try {
            $storeNotice->update($request->only($this->dataArray));
            $this->syncUsersOrShops($request, $storeNotice);
            $this->syncReadStatus($storeNotice);
            event(new StoreNoticeEvent($storeNotice, 'update'));
            return $storeNotice;
        } catch (Exception $e) {
            throw new Exception(SOMETHING_WENT_WRONG);
        }
    }
}
