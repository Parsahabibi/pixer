<?php


namespace Marvel\GraphQL\Mutation;

use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Marvel\Exceptions\MarvelException;
use Marvel\Facades\Shop;

class OrderMutator
{

    public function store($rootValue, array $args, GraphQLContext $context)
    {
        return Shop::call('Marvel\Http\Controllers\OrderController@store', $args);
    }
    public function update($rootValue, array $args, GraphQLContext $context)
    {
        return Shop::call('Marvel\Http\Controllers\OrderController@updateOrderGql', $args);
    }
    public function generateInvoiceDownloadUrl($rootValue, array $args, GraphQLContext $context)
    {
        return Shop::call('Marvel\Http\Controllers\OrderController@downloadInvoiceUrl', $args);
    }
    public function createOrderPayment($rootValue, array $args, GraphQLContext $context)
    {
        return Shop::call('Marvel\Http\Controllers\OrderController@submitPayment', $args);
    }
}
