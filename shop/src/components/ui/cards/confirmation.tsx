import { TrashIcon } from '@/components/icons/trash-icon';
import Button from '@/components/ui/button';
import { useTranslation } from 'next-i18next';
import cn from 'classnames';

type ConfirmationCardProps = {
  onCancel: () => void;
  onDelete: () => void;
  title?: string;
  icon?: any;
  description?: string;
  cancelBtnClassName?: string;
  deleteBtnClassName?: string;
  cancelBtnText?: string;
  deleteBtnText?: string;
  cancelBtnLoading?: boolean;
  deleteBtnLoading?: boolean;
};

const Confirmation: React.FC<ConfirmationCardProps> = ({
  onCancel,
  onDelete,
  icon,
  title = 'text-delete',
  description = 'text-card-delete-confirm',
  cancelBtnText = 'text-cancel',
  deleteBtnText = 'text-delete',
  cancelBtnClassName,
  deleteBtnClassName,
  cancelBtnLoading,
  deleteBtnLoading,
}) => {
  const { t } = useTranslation('common');
  return (
    <div className="flex h-full min-h-screen w-screen flex-col justify-center bg-light p-7 text-left dark:bg-dark-250 xs:h-auto xs:min-h-0 sm:w-96 md:rounded-xl">
      <div className="h-full w-full text-center">
        <div className="flex h-full flex-col justify-between">
          <span className="m-auto mt-4 text-brand">
            {icon ? icon : <TrashIcon className="h-14 w-14" />}
          </span>
          <p className="mt-4 mb-1 text-xl font-bold text-black dark:text-light">
            {t(title)}
          </p>
          <p className="mb-1 px-6 py-2 leading-relaxed">{t(description)}</p>
          <div className="mt-7 flex w-full items-center justify-between space-x-4 rtl:space-x-reverse">
            <div className="w-1/2">
              <Button
                onClick={onCancel}
                isLoading={cancelBtnLoading}
                disabled={cancelBtnLoading}
                className={cn('w-full', cancelBtnClassName)}
              >
                {t(cancelBtnText)}
              </Button>
            </div>

            <div className="w-1/2">
              <Button
                onClick={onDelete}
                isLoading={deleteBtnLoading}
                disabled={deleteBtnLoading}
                className={cn(
                  'w-full rounded bg-red-600 py-2 px-4 text-center text-base font-semibold text-light shadow-md transition duration-200 ease-in hover:bg-red-700 focus:bg-red-700 focus:outline-none',
                  deleteBtnClassName
                )}
              >
                {t(deleteBtnText)}
              </Button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Confirmation;
