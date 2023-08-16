import { useTranslation } from 'next-i18next';
import UserBoxHeaderView from '@/components/message/user-box-header';
import UserListView from '@/components/message/user-list';
import Button from '@/components/ui/button';
import cn from 'classnames';
import { useModalAction } from '@/components/ui/modal/modal.context';
import { useWindowSize } from '@/utils/use-window-size';
import { RESPONSIVE_WIDTH } from '@/utils/constants';
import { useState } from 'react';
import { adminOnly, getAuthCredentials, hasAccess } from '@/utils/auth-utils';
interface Props {
  className?: string;
}

const UserListIndex = ({ className, ...rest }: Props) => {
  const { t } = useTranslation();
  const { openModal } = useModalAction();
  const [text, setText] = useState('');
  const { width } = useWindowSize();
  const { permissions } = getAuthCredentials();
  let adminPermission = hasAccess(adminOnly, permissions);
  function handleComposeClick() {
    openModal('COMPOSE_MESSAGE');
  }
  return (
    <>
      <div
        className={cn(
          width >= RESPONSIVE_WIDTH
            ? 'max-w-[4rem] border-r border-solid border-r-[#E5E7EB] sm:max-w-xs 2xl:max-w-[26rem] '
            : '',
          'flex h-full flex-1 flex-col',
          adminPermission ? 'pb-6' : '',
          className
        )}
        {...rest}
      >
        {/* header search view */}
        <UserBoxHeaderView
          onChange={(event: any) => setText(event?.target?.value)}
          value={text}
          clear={() => setText('')}
        />

        {/* conversation list view */}
        <UserListView filterText={text} permission={adminPermission} />

        {adminPermission ? (
          <div className="mx-6 block">
            <Button onClick={handleComposeClick} className="w-full">
              {t('text-compose')}
            </Button>
          </div>
        ) : (
          ''
        )}
      </div>
    </>
  );
};

export default UserListIndex;
