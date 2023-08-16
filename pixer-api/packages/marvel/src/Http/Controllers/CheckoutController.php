<?php

namespace Marvel\Http\Controllers;

use Marvel\Database\Repositories\CheckoutRepository;
use Marvel\Exceptions\MarvelException;
use Marvel\Http\Requests\CheckoutVerifyRequest;

class CheckoutController extends CoreController
{
    public $repository;

    public function __construct(CheckoutRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Verify the checkout data and calculate tax and shipping.
     *
     * @param CheckoutVerifyRequest $request
     * @return array
     */
    public function verify(CheckoutVerifyRequest $request)
    {
        try {
            return $this->repository->verify($request);
        } catch (MarvelException $th) {
            throw new MarvelException(SOMETHING_WENT_WRONG);
        }
    }
}
