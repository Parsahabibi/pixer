<?php

namespace Marvel\Database\Repositories;

use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Marvel\Database\Models\AbusiveReport;
use Marvel\Exceptions\MarvelException;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AbusiveReportRepository extends BaseRepository
{
    /**
     * @var array[]
     */
    protected $dataArray = [
        'user_id',
        'model_type',
        'model_id',
        'message'
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
        return AbusiveReport::class;
    }
    /**
     * @param $request
     * @return LengthAwarePaginator|JsonResponse|Collection|mixed
     */
    public function storeAbusiveReport($request, $model)
    {
        try {
            $model_id   = $request['model_id'];
            $model_type = $request['model_type'];
            $model_name = "Marvel\\Database\\Models\\{$model_type}";

            if (!empty($this->where('model_id', $model_id)->where('model_type', $model_name)->firstOrFail())) {
                throw new BadRequestHttpException(YOU_HAVE_ALREADY_GIVEN_ABUSIVE_REPORT_FOR_THIS);
            }
            return $model->abusive_reports()->create($request->only($this->dataArray));
        } catch (Exception $th) {
            throw new HttpException(400, COULD_NOT_CREATE_THE_RESOURCE);
        }
    }
}
