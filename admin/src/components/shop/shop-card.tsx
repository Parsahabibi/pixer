import Image from 'next/image';
import { useTranslation } from 'next-i18next';
import Link from '@/components/ui/link';
import Badge from '@/components/ui/badge/badge';

type ShopCardProps = {
  shop: any;
};

const ShopCard: React.FC<ShopCardProps> = ({ shop }) => {
  const { t } = useTranslation();

  const isNew = false;

  return (
    <Link href={`/${shop?.slug}`}>
      <div className="relative flex cursor-pointer items-center rounded border border-gray-200 bg-light p-5">
        {isNew && (
          <span className="absolute top-2 rounded bg-blue-500 px-2 py-1 text-xs text-light end-2">
            {t('common:text-new')}
          </span>
        )}
        <div className="relative flex h-16 w-16 flex-shrink-0 items-center justify-center overflow-hidden rounded-full border border-gray-100 bg-gray-300">
          <Image
            alt={t('common:text-logo')}
            src={
              shop?.logo?.thumbnail! ?? '/product-placeholder-borderless.svg'
            }
            fill
            sizes="(max-width: 768px) 100vw"
            className="object-cover"
          />
        </div>

        <div className="flex flex-col ms-4">
          <span className="mb-2 text-lg font-semibold text-heading">
            {shop?.name}
          </span>
          <span>
            <Badge
              textKey={
                shop?.is_active ? 'common:text-active' : 'common:text-inactive'
              }
              color={shop?.is_active ? 'bg-accent' : 'bg-red-500'}
            />
          </span>
        </div>
      </div>
    </Link>
  );
};

export default ShopCard;
