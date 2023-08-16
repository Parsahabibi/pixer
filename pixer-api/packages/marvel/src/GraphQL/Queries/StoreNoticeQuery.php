<?php


namespace Marvel\GraphQL\Queries;


use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Marvel\Facades\Shop;

class StoreNoticeQuery
{
    public function fetchStoreNotices($rootValue, array $args, GraphQLContext $context)
    {
        return Shop::call('Marvel\Http\Controllers\StoreNoticeController@fetchStoreNotices', $args);
    }

    public function getStoreNoticeType($rootValue, array $args, GraphQLContext $context)
    {
        return Shop::call('Marvel\Http\Controllers\StoreNoticeController@getStoreNoticeType', $args);
    }

    public function getUsersToNotify($rootValue, array $args, GraphQLContext $context)
    {
        return Shop::call('Marvel\Http\Controllers\StoreNoticeController@getUsersToNotify', $args);
    }
    
}
