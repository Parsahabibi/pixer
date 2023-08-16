import type {
  FollowedShopsQueryOptions,
  Shop,
  ShopPaginator,
  TopShopQueryOptions,
} from '@/types';
import type { UseInfiniteQueryOptions } from 'react-query';
import { useInfiniteQuery, useQuery } from 'react-query';
import { API_ENDPOINTS } from '@/data/client/endpoints';
import client from '@/data/client';
import { FollowShopPopularProductsQueryOption, Product } from '@/types';
import { useRouter } from 'next/router';

export function useTopShops(
  options?: Partial<TopShopQueryOptions>,
  config?: UseInfiniteQueryOptions<ShopPaginator, Error>
) {
  const { locale } = useRouter();

  const formattedOptions = {
    ...options,
    language: locale,
  };

  const {
    data,
    isLoading,
    error,
    fetchNextPage,
    hasNextPage,
    isFetching,
    isFetchingNextPage,
  } = useInfiniteQuery<ShopPaginator, Error>(
    [API_ENDPOINTS.TOP_SHOPS, formattedOptions],
    ({ queryKey, pageParam }) =>
      client.shops.top(Object.assign({}, queryKey[1], pageParam)),
    {
      ...config,
      getNextPageParam: ({ current_page, last_page }) =>
        last_page > current_page && { page: current_page + 1 },
    }
  );

  function handleLoadMore() {
    fetchNextPage();
  }

  return {
    shops: data?.pages.flatMap((page) => page.data) ?? [],
    isLoading,
    error,
    hasNextPage,
    isFetching,
    isLoadingMore: isFetchingNextPage,
    loadMore: handleLoadMore,
  };
}

export function useShop(slug: string) {
  const { data, isLoading, error } = useQuery<Shop, Error>(
    [API_ENDPOINTS.SHOPS, slug],
    () => client.shops.get(slug)
  );
  return {
    product: data,
    isLoading,
    error,
  };
}

export function useFollowedShops(
  options?: Partial<FollowedShopsQueryOptions>,
  config?: UseInfiniteQueryOptions<ShopPaginator, Error>
) {
  const {
    data,
    isLoading,
    error,
    fetchNextPage,
    hasNextPage,
    isFetching,
    isFetchingNextPage,
  } = useInfiniteQuery<ShopPaginator, Error>(
    [API_ENDPOINTS.FOLLOWED_SHOPS, options],
    ({ queryKey, pageParam }) =>
      client.follow.shops(Object.assign({}, queryKey[1], pageParam)),
    {
      ...config,
      getNextPageParam: ({ current_page, last_page }) =>
        last_page > current_page && { page: current_page + 1 },
    }
  );

  function handleLoadMore() {
    fetchNextPage();
  }

  return {
    shops: data?.pages.flatMap((page) => page.data) ?? [],
    paginatorInfo: Array.isArray(data?.pages)
      ? data?.pages[data.pages.length - 1]
      : null,
    isLoading,
    error,
    hasNextPage,
    isFetching,
    isLoadingMore: isFetchingNextPage,
    loadMore: handleLoadMore,
  };
}

export function useFollowedShopsProducts(
  options: Partial<FollowShopPopularProductsQueryOption>
) {
  const { locale } = useRouter();

  const formattedOptions = {
    ...options,
    language: locale,
  };

  const { data, isLoading, error } = useQuery<Product[], Error>(
    [API_ENDPOINTS.FOLLOWED_SHOPS_PRODUCTS, formattedOptions],
    ({ queryKey }) =>
      client.follow.followedShopProducts(Object.assign({}, queryKey[1]))
  );

  return {
    products: data ?? [],
    isLoading,
    error,
  };
}
