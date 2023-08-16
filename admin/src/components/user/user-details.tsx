import Image from 'next/image';
import { CheckMarkFill } from '@/components/icons/checkmark-circle-fill';
import { CloseFillIcon } from '@/components/icons/close-fill';
import { useTranslation } from 'next-i18next';
import Link from '@/components/ui/link';
import { Routes } from '@/config/routes';
import Loader from '@/components/ui/loader/loader';
import { useMeQuery } from '@/data/user';

const UserDetails: React.FC = () => {
  const { t } = useTranslation('common');
  const { data, isLoading: loading } = useMeQuery();
  if (loading) return <Loader text={t('text-loading')} />;

  return (
    <div className="flex h-full flex-col items-center p-5">
      <div className="relative flex h-32 w-32 shrink-0 items-center justify-center overflow-hidden rounded-full border border-gray-200">
        <Image
          src={data?.profile?.avatar?.thumbnail ?? '/avatar-placeholder.svg'}
          fill
          sizes="(max-width: 768px) 100vw"
          alt={data?.name ?? ''}
        />
      </div>
      <h3 className="mt-4 text-lg font-semibold text-heading">{data?.name}</h3>
      <p className="mt-1 text-sm text-muted">{data?.email}</p>
      {!data?.profile ? (
        <p className="mt-0.5 text-sm text-muted">
          {t('text-add-your')}{' '}
          <Link href={Routes.profileUpdate} className="text-accent underline">
            {t('authorized-nav-item-profile')}
          </Link>
        </p>
      ) : (
        <>
          <p className="mt-0.5 text-sm text-muted">{data?.profile.contact}</p>
        </>
      )}
      <div className="mt-6 flex items-center justify-center rounded border border-gray-200 px-3 py-2 text-sm text-body-dark">
        {data?.is_active ? (
          <CheckMarkFill width={16} className="text-accent me-2" />
        ) : (
          <CloseFillIcon width={16} className="text-red-500 me-2" />
        )}
        {data?.is_active ? 'Enabled' : 'Disabled'}
      </div>
    </div>
  );
};
export default UserDetails;
