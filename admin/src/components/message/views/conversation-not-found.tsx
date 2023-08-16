import { useTranslation } from 'next-i18next';
import { EmptyInbox } from '@/components/icons/empty-inbox';

const UserListNotFound = () => {
  const { t } = useTranslation();
  return (
    <>
      <div className="flex flex-auto items-center justify-center pb-6 md:pb-10">
        <div className="px-5 text-center">
          <div className="mb-10">
            <EmptyInbox className="mx-auto" />
          </div>
          <p className="font-medium text-[#686D73]">{t('text-inbox-empty')}</p>
        </div>
      </div>
    </>
  );
};

export default UserListNotFound;
