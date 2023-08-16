import type { Type, TypeQueryOptions } from '@/types';
import { useInfiniteQuery } from 'react-query';
import { API_ENDPOINTS } from '@/data/client/endpoints';
import client from '@/data/client';
import { useRouter } from 'next/router';

export function useTypes(options?: TypeQueryOptions) {
  const { locale } = useRouter();

  const formattedOptions = {
    ...options,
    language: locale,
  };

  const { data, isLoading, error } = useInfiniteQuery<Type[], Error>(
    [API_ENDPOINTS.TYPES, formattedOptions],
    ({ queryKey, pageParam }) =>
      client.types.all(Object.assign({}, queryKey[1], pageParam))
  );

  return {
    types: data?.pages.flatMap((data) => data) ?? [],
    isLoading,
    error,
  };
}
