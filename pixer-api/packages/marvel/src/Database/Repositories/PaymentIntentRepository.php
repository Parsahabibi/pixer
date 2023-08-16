<?php


namespace Marvel\Database\Repositories;

use Marvel\Database\Models\PaymentIntent;
use Marvel\Traits\PaymentTrait;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;


class PaymentIntentRepository extends BaseRepository
{
    use PaymentTrait;
    /**
     * @var string[]
     */
    protected $dataArray = [
        'tracking_number',
    ];

    /**
     * boot
     *
     * @return void
     */
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
        return PaymentIntent::class;
    }

    /**
     * getPaymentIntent
     *
     * @param  mixed $request
     * @param  mixed $settings
     * @return void
     */
    public function getPaymentIntent($request, $settings)
    {
        return $this->processPaymentIntent($request, $settings);
    }
}
