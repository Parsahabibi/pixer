<?php


namespace Marvel\GraphQL\Mutation;


use Marvel\Exceptions\MarvelException;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Marvel\Facades\Shop;

class PaymentIntentMutator
{
    /**
     * @throws MarvelException
     */
    public function savePaymentMethod($rootValue, array $args, GraphQLContext $context)
    {
        try {
            return Shop::call('Marvel\Http\Controllers\PaymentMethodController@savePaymentMethod', $args);
        } catch (\Exception $e) {
            throw new MarvelException(SOMETHING_WENT_WRONG);
        }
    }
}
