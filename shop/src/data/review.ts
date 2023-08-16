import { useQuery } from 'react-query';
import { ReviewPaginator, ReviewQueryOptions } from '@/types';
import { API_ENDPOINTS } from '@/data/client/endpoints';
import client from '@/data/client';
import { mapPaginatorData } from '@/data/utils/data-mapper';

export function useReviews(options: ReviewQueryOptions) {
  const {
    data: response,
    isLoading,
    error,
    isFetching,
  } = useQuery<ReviewPaginator, Error>(
    [API_ENDPOINTS.PRODUCTS_REVIEWS, options],
    ({ queryKey }) =>
      client.reviews.all(Object.assign({}, queryKey[1] as ReviewQueryOptions)),
    {
      keepPreviousData: true,
    }
  );
  return {
    reviews: response?.data ?? [],
    paginatorInfo: mapPaginatorData(response),
    isLoading,
    error,
    isFetching,
    hasMore: response && response?.last_page > response?.current_page,
  };
}
