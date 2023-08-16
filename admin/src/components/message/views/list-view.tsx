import cn from 'classnames';
import { useRouter } from 'next/router';
import React from 'react';
import isEmpty from 'lodash/isEmpty';
import Image from 'next/image';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';
import { Routes } from '@/config/routes';
import { Conversations } from '@/types';
import { MessageAvatarPlaceholderIcon } from '@/components/icons/message-avatar-placeholder-icon';
import { adminOnly, getAuthCredentials, hasAccess } from '@/utils/auth-utils';
import { useMessageSeen } from '@/data/conversations';

dayjs.extend(relativeTime);
dayjs.extend(utc);
dayjs.extend(timezone);

interface Props {
  conversation: Conversations;
  className?: string;
}

const UserListView = ({ conversation, className, ...rest }: Props) => {
  const router = useRouter();
  const { mutate: createSeenMessage } = useMessageSeen();
  const { permissions } = getAuthCredentials();
  let permission = hasAccess(adminOnly, permissions);
  const routes = permission
    ? Routes?.message?.details(conversation?.id)
    : Routes?.shopMessage?.details(conversation?.id);
  const seenMessage = (unseen: boolean) => {
    if (unseen) {
      createSeenMessage({
        id: conversation?.id,
      });
    }
  };
  return (
    <>
      <div
        className={cn(
          'relative cursor-pointer border-b border-solid border-b-[#E5E7EB] transition-all duration-500 hover:bg-[#e4e5e7]',
          Number(router?.query?.id) === Number(conversation?.id)
            ? 'bg-[#F3F4F6]'
            : '',
          Boolean(conversation?.shop?.is_active) ? '' : 'bg-[#e6e7ea]',
          className
        )}
        onClick={() => {
          router.push(`${routes}`);
          seenMessage(Boolean(conversation?.unseen));
        }}
        {...rest}
      >
        {Boolean(conversation?.unseen) ? (
          <div className="absolute left-2 top-1/2 z-50 h-[.375rem] w-[.375rem] -translate-y-1/2 transform rounded-full bg-[#EF4444]"></div>
        ) : (
          ''
        )}
        <div
          className={cn(
            'flex w-full gap-x-3 p-3 sm:p-6',
            !isEmpty(conversation?.latest_message?.body) ? 'items-center' : ''
          )}
        >
          <div className="relative h-8 w-8 overflow-hidden rounded-full 2xl:h-10 2xl:w-10">
            {!isEmpty(conversation?.shop?.logo?.thumbnail) ? (
              <Image
                // @ts-ignore
                src={conversation?.shop?.logo?.thumbnail}
                alt={String(conversation?.shop?.name)}
                fill
                sizes="(max-width: 768px) 100vw"
                className="product-image object-contain"
              />
            ) : (
              <MessageAvatarPlaceholderIcon
                className="text-[2rem] 2xl:text-[2.5rem]"
                color="#DDDDDD"
              />
            )}
          </div>
          <div className="block w-10/12">
            <div className="flex items-center justify-between">
              {isEmpty(conversation?.latest_message?.body) ? (
                <h2 className="mr-1 w-[70%] truncate text-sm font-semibold">
                  {conversation?.shop?.name}
                </h2>
              ) : (
                <h2 className="mr-1 w-[70%] truncate text-sm font-semibold">
                  {conversation?.latest_message?.body}
                </h2>
              )}

              {conversation?.latest_message?.created_at ? (
                <p className="truncate text-xs text-[#686D73]">
                  {dayjs().to(
                    dayjs.utc(conversation?.latest_message?.created_at)
                  )}
                </p>
              ) : (
                ''
              )}
            </div>
            {!isEmpty(conversation?.latest_message?.body) ? (
              <p className="text-xs text-[#64748B]">
                {conversation?.shop?.name}
              </p>
            ) : (
              ''
            )}
          </div>
        </div>
      </div>
    </>
  );
};

export default UserListView;
