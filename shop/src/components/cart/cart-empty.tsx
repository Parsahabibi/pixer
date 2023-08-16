import cn from 'classnames';
import { EmptyCartIcon } from '@/components/icons/empty-cart-icon';
import { useTranslation } from 'next-i18next';

interface Props {
  title?: string;
  description?: string;
  className?: string;
}

export default function CartEmpty({
  title = 'text-empty-cart-message',
  description = 'text-empty-cart-list',
  className,
}: Props) {
  const { t } = useTranslation('common');
  return (
    <div
      className={cn(
        'flex h-full flex-col items-center justify-center',
        className
      )}
    >
      <EmptyCartIcon className="h-[100px] w-[100px] flex-shrink-0 text-light-800 dark:text-dark-600" />
      <h4 className="mt-8 text-sm font-medium text-dark dark:text-light 3xl:mt-9">
        {t(title)}
      </h4>
      <p className="mt-2 text-13px text-light-base dark:text-dark-800 md:mt-3">
        {t(description)}
      </p>
    </div>
  );
}
