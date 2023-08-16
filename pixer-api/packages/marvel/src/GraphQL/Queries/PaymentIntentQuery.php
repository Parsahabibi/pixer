<?php


namespace Marvel\GraphQL\Queries;

use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Marvel\Facades\Shop;

class PaymentIntentQuery
{
    public function getPaymentIntent($rootValue, array $args, GraphQLContext $context)
    {
        return Shop::call('Marvel\Http\Controllers\PaymentIntentController@getPaymentIntent', $args);
    }
}
