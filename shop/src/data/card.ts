import { useQuery, useQueryClient, useMutation } from 'react-query';
import { Card } from '@/types';
import { API_ENDPOINTS } from '@/data/client/endpoints';
import client from '@/data/client';
import toast from 'react-hot-toast';
import { useModalAction } from '@/components/modal-views/context';
import { useTranslation } from 'next-i18next';
import { useMe } from '@/data/user';

export function useCards(params?: any, options?: any) {
  const { isAuthorized } = useMe();

  const { data, isLoading, error } = useQuery<Card[], Error>(
    [API_ENDPOINTS.CARDS, params],
    () => client.cards.all(params),
    {
      enabled: isAuthorized,
      ...options,
    }
  );

  return {
    cards: data ?? [],
    isLoading,
    error,
  };
}

export const useDeleteCard = () => {
  const queryClient = useQueryClient();
  const { t } = useTranslation();
  const { closeModal } = useModalAction();

  const { mutate, isLoading, error } = useMutation(client.cards.remove, {
    onSuccess: () => {
      closeModal();
      toast.success(t('common:text-card-successfully-deleted'));
    },
    // Always refetch after error or success:
    onSettled: () => {
      queryClient.invalidateQueries(API_ENDPOINTS.CARDS);
    },
  });

  return {
    deleteCard: mutate,
    isLoading,
    error,
  };
};

export function useAddCards(method_key?: any) {
  const { t } = useTranslation();
  const { closeModal } = useModalAction();
  const queryClient = useQueryClient();
  const { mutate, isLoading, error } = useMutation(
    client.cards.addPaymentMethod,
    {
      onSuccess: () => {
        closeModal();
        toast.success(t('common:text-card-successfully-add'), {
          id: 'success',
        });
      },
      onError: (error) => {
        const {
          response: { data },
        }: any = error ?? {};

        toast.error(t(data?.message), {
          id: 'error',
        });
      },
      // Always refetch after error or success:
      onSettled: () => {
        queryClient.invalidateQueries(API_ENDPOINTS.CARDS);
      },
    }
  );

  return {
    addNewCard: mutate,
    isLoading,
    error,
  };
}

export function useDefaultPaymentMethod() {
  const { t } = useTranslation();
  const queryClient = useQueryClient();
  const { mutate, isLoading, error } = useMutation(
    client.cards.makeDefaultPaymentMethod,
    {
      onSuccess: () => {
        toast.success(t('common:text-set-default-card-message'));
      },
      // Always refetch after error or success:
      onSettled: () => {
        queryClient.invalidateQueries(API_ENDPOINTS.CARDS);
      },
    }
  );

  return {
    createDefaultPaymentMethod: mutate,
    isLoading,
    error,
  };
}
