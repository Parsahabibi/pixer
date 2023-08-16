<?php


namespace Marvel\GraphQL\Queries;


use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Marvel\Facades\Shop;

class OrderQuery
{
    public function fetchOrders($rootValue, array $args, GraphQLContext $context)
    {
        return Shop::call('Marvel\Http\Controllers\OrderController@fetchOrders', $args);
    }

    public function fetchSingleOrder($rootValue, array $args, GraphQLContext $context)
    {
        return Shop::call('Marvel\Http\Controllers\OrderController@fetchSingleOrder', $args);
    }
}
