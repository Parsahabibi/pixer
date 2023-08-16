<?php


namespace Marvel\Database\Repositories;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Marvel\Database\Models\StoreNoticeRead;
use Marvel\Exceptions\MarvelException;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class StoreNoticeReadRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [];

    /**
     * @var array
     */
    protected $dataArray = [
        'store_notice_id',
        'user_id',
        'is_read',
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
        return StoreNoticeRead::class;
    }


    /**
     * Store or Update a newly created resource in storage.
     * This method will update read_status of a single StoreNotice for requested user { id in requestBody }.
     * @param Request $request
     * @return mixed
     * @throws MarvelException
     */
    public function readSingleNotice(Request $request)
    {
        try {
            $id = $request->id;
            $userId = $request->user()->id;
            $exists = $this->where('store_notice_id', $id)->where('user_id', $userId)->count();
            if ($exists) {
                $this->where('store_notice_id', $id)->where('user_id', $userId)->delete();
            }
            $update = $this->updateOrCreate(
                [
                    'store_notice_id' => $id,
                    'user_id'         => $request->user()->id,
                    'is_read'         => true
                ]
            );
            if (!$update) {
                throw new ModelNotFoundException(NOT_FOUND);
            }
            return $update;
        } catch (Exception $e) {
            throw new Exception(SOMETHING_WENT_WRONG);
        }
    }

    /**
     * Store or Update resources in storage.
     * This method will update read_status of a multiple StoreNotice for requested user { array of id in requestBody }.
     * @param Request $request
     * @return mixed
     * @throws MarvelException
     */
    public function readAllNotice(Request $request)
    {

        try {
            $userId = $request->user()->id;
            $noticeIdArr = $request->notices;
            $exists = $this->whereIn('store_notice_id', $noticeIdArr)->where('user_id', $userId)->count();
            if ($exists) {
                $this->whereIn('store_notice_id', $noticeIdArr)->where('user_id', $userId)->delete();
            }
            $insertionArr = Arr::map($noticeIdArr, fn ($noticeId) => [
                'store_notice_id' => $noticeId,
                'user_id'         => $userId,
                'is_read'         => true
            ]);
            $insert = $this->insert($insertionArr);
            if (!$insert) {
                throw new HttpException(400, NOT_FOUND);
            }
            return $this->whereIn('store_notice_id', $noticeIdArr)->where('user_id', $userId)->get();
        } catch (Exception $e) {
            throw new HttpException(400, SOMETHING_WENT_WRONG);
        }
    }
}
