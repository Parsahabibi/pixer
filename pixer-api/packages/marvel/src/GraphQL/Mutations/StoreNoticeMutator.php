<?php


namespace Marvel\GraphQL\Mutation;

use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Marvel\Facades\Shop;

class StoreNoticeMutator
{

    public function createStoreNotice($rootValue, array $args, GraphQLContext $context)
    {
        return Shop::call('Marvel\Http\Controllers\StoreNoticeController@store', $args);
    }

    public function updateStoreNotice($rootValue, array $args, GraphQLContext $context)
    {
        return Shop::call('Marvel\Http\Controllers\StoreNoticeController@updateStoreNotice', $args);
    }
    public function deleteStoreNotice($rootValue, array $args, GraphQLContext $context)
    {
        return Shop::call('Marvel\Http\Controllers\StoreNoticeController@deleteStoreNotice', $args);
    }
    public function readNotice($rootValue, array $args, GraphQLContext $context)
    {
        return Shop::call('Marvel\Http\Controllers\StoreNoticeController@readNotice', $args);
    }
    public function readAllNotice($rootValue, array $args, GraphQLContext $context)
    {
        return Shop::call('Marvel\Http\Controllers\StoreNoticeController@readAllNotice', $args);
    }
}
