<?php

namespace Marvel\Http\Controllers;

use Illuminate\Http\Request;
use Marvel\Database\Models\PaymentMethod;
use Marvel\Database\Repositories\PaymentMethodRepository;
use Marvel\Database\Models\Settings;
use Marvel\Exceptions\MarvelException;
use Marvel\Facades\Payment;
use Marvel\Http\Requests\PaymentMethodCreateRequest;
use Marvel\Traits\PaymentTrait;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PaymentMethodController extends CoreController
{

    use PaymentTrait;

    /**
     * repository
     *
     * @var mixed
     */
    public $repository;

    /**
     * settings
     *
     * @var mixed
     */
    public $settings;

    public function __construct(PaymentMethodRepository $repository)
    {
        $this->repository = $repository;
        $this->settings = Settings::first();
    }

    /**
     * index
     * 
     * Get all the available payment method (e.g. Card) of current customer.
     *
     * @param  mixed $request
     * @return void
     */
    public function index(Request $request)
    {
        $user = $request->user();
        return $this->repository->with('payment_gateways')->whereRelation('payment_gateways', 'user_id', $user->id)->whereRelation('payment_gateways', 'gateway_name', $this->settings->options['paymentGateway'])->get();
    }

    /**
     * store
     * 
     * Create & store the payment method (e.g. Card) and store the only available & safe information in database.
     *
     * @param  mixed $request
     * @return void
     */
    public function store(PaymentMethodCreateRequest $request)
    {
        try {
            return $this->repository->storeCards($request, $this->settings);
        } catch (MarvelException $e) {
            throw new MarvelException(COULD_NOT_CREATE_THE_RESOURCE);
        }
    }

    /**
     * destroy
     * 
     * Delete Payment method (e.g. Card) from a user.
     *
     * @param  Request  $request
     * @param  mixed  $id
     * @return void
     * @throws \Exception
     */
    public function destroy(Request $request, mixed $id)
    {
        $request->id = $id;
        return $this->deletePaymentMethod($request);
    }


    public function deletePaymentMethod(Request $request)
    {
        try {
            try {
                $retrieved_payment_method = PaymentMethod::where('id', '=', $request->id)->first();
                Payment::detachPaymentMethodToCustomer($retrieved_payment_method->method_key);
                return $this->repository->findOrFail($request->id)->forceDelete();
            } catch (\Exception $e) {
                throw new HttpException(409, COULD_NOT_DELETE_THE_RESOURCE);
            }
        } catch (MarvelException $e) {
            throw new MarvelException(COULD_NOT_DELETE_THE_RESOURCE, $e->getMessage());
        }
    }

    /**
     * getMethodKeyByCard
     * 
     * When creating a payment method (e.g Card) during checkout, it needs to generate that payment method identifier. 
     * It can be used, in case of payment methods where cards can be saved.
     *
     * @param  mixed $request
     * @return void
     */
    public function savePaymentMethod(Request $request)
    {
        switch ($this->settings->options['paymentGateway']) {
            case 'stripe':
                return $this->repository->saveStripeCard($request, $this->settings);
                break;
        }
    }

    /**
     * saveCardIntent
     * 
     * Save payment method (e.g. Card) for future usages.
     *
     * @param  mixed $request
     * @return void
     */
    public function saveCardIntent(Request $request)
    {
        switch ($this->settings->options['paymentGateway']) {
            case 'stripe':
                $setupIntent = $this->repository->setStripeIntent($request);
                break;
        }

        return $setupIntent;
    }

    /**
     * setDefaultPaymentMethod
     * 
     * This method initiate the functionalities to set a payment method (e.g. Card) as a default for any user.
     *
     * @param  mixed $request
     * @return void
     */
    public function setDefaultCard(Request $request)
    {
        try {
            return $this->repository->setDefaultPaymentMethod($request);
        } catch (MarvelException $e) {
            throw new MarvelException(COULD_NOT_CREATE_THE_RESOURCE);
        }
        // if system varies from payment-gateway to payment-gateway, then use this.
        // switch ($this->settings->options['paymentGateway']) {
        //     case 'stripe':
        //         $setDefaultPayment = $this->repository->setDefaultPaymentMethod($request);
        //         break;
        // }
    }
}
