import type {
  FollowShopPopularProductsQueryOption,
  PopularProductsQueryOptions,
  Product,
  ProductPaginator,
  ProductQueryOptions,
} from '@/types';
import type { UseInfiniteQueryOptions } from 'react-query';
import {
  useInfiniteQuery,
  useMutation,
  useQuery,
  useQueryClient,
} from 'react-query';
import { API_ENDPOINTS } from '@/data/client/endpoints';
import client from '@/data/client';
import toast from 'react-hot-toast';
import { useRouter } from 'next/router';

export function useProducts(
  options?: Partial<ProductQueryOptions>,
  config?: UseInfiniteQueryOptions<ProductPaginator, Error>
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
  } = useInfiniteQuery<ProductPaginator, Error>(
    [API_ENDPOINTS.PRODUCTS, formattedOptions],
    ({ queryKey, pageParam }) =>
      client.products.all(Object.assign({}, queryKey[1], pageParam)),
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
    products: data?.pages.flatMap((page) => page.data) ?? [],
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

export function useProduct(slug: string) {
  const { locale: language } = useRouter();

  // console.log({ slug, language });

  const { data, isLoading, error } = useQuery<Product, Error>(
    [API_ENDPOINTS.PRODUCTS, { slug, language }],
    () => client.products.get({ slug, language })
  );
  return {
    product: data,
    isLoading,
    error,
  };
}

export function usePopularProducts(
  options?: Partial<PopularProductsQueryOptions>
) {
  const { locale } = useRouter();

  const formattedOptions = {
    ...options,
    language: locale,
  };

  const { data, isLoading, error } = useQuery<Product[], Error>(
    [API_ENDPOINTS.PRODUCTS_POPULAR, formattedOptions],
    ({ queryKey }) => client.products.popular(Object.assign({}, queryKey[1]))
  );
  return {
    popularProducts: data ?? [],
    isLoading,
    error,
  };
}

export function useCreateFeedback() {
  const queryClient = useQueryClient();

  const { mutate, isLoading, error } = useMutation(client.feedback.create, {
    onSuccess: () => {
      toast.success('Feedback Submitted');
      queryClient.refetchQueries(API_ENDPOINTS.PRODUCTS_QUESTIONS);
      queryClient.refetchQueries(API_ENDPOINTS.PRODUCTS_REVIEWS);
    },
  });

  return {
    createFeedback: mutate,
    isLoading,
    error,
  };
}
