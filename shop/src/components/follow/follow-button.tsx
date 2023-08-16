import Button from '@/components/ui/button';
import { UserFollowingIcon } from '@/components/icons/user-following-icon';
import { useMutation, useQuery, useQueryClient } from 'react-query';
import { useMe } from '@/data/user';
import { API_ENDPOINTS } from '@/data/client/endpoints';
import client from '@/data/client';
import { CloseCircleIcon } from '../icons/close-circle-icon';
import { useTranslation } from 'next-i18next';

export default function FollowButton({ shop_id }: { shop_id: string }) {
  const queryClient = useQueryClient();
  const { isAuthorized, isLoading, me } = useMe();
  const { data: isShopFollowed, isLoading: isFollowLoading } = useQuery(
    [API_ENDPOINTS.FOLLOW_SHOP, shop_id],
    () => client.follow.isShopFollowed({ shop_id }),
    {
      enabled: isAuthorized,
    }
  );

  const { mutate: toggleFollow } = useMutation(client.follow.toggle, {
    onSettled: () => {
      queryClient.invalidateQueries([API_ENDPOINTS.FOLLOW_SHOP, shop_id]);
      queryClient.invalidateQueries([API_ENDPOINTS.FOLLOWED_SHOPS]);
    },
  });

  function handleToggleFollow() {
    toggleFollow({
      shop_id,
    });
  }
  const { t } = useTranslation('common');
  return (
    <>
      {isAuthorized && me && !isLoading && (
        <Button
          onClick={handleToggleFollow}
          variant={isShopFollowed ? 'solidDanger' : 'outline'}
          className={`followButton h-9 min-h-[36px] rounded-md p-2 px-3 text-xs sm:h-11 sm:min-h-[44px] md:px-4 ${
            !isShopFollowed && 'text-brand dark:text-brand'
          }`}
          isLoading={isFollowLoading}
        >
          {!isFollowLoading && (
            <>
              {isShopFollowed ? (
                <>
                  <CloseCircleIcon className="h-[15px] w-[15px] text-current" />
                  {t('text-unfollow')}
                </>
              ) : (
                <>
                  <UserFollowingIcon className="h-4 w-4 text-current" />{' '}
                  {t('text-follow')}
                </>
              )}
            </>
          )}
        </Button>
      )}
    </>
  );
}
