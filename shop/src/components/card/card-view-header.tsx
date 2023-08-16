import { useModalAction } from '@/components/modal-views/context';
import { PlusIcon } from '@/components/icons/plus-icon';
import { useTranslation } from 'next-i18next';
import { useSettings } from '@/data/settings';

const CardViewHeader = () => {
  const { openModal } = useModalAction();
  const { t } = useTranslation('common');
  const { settings } = useSettings();

  const handleAddNewCard = () => {
    openModal('ADD_NEW_CARD', { paymentGateway: settings?.paymentGateway });
  };

  return (
    <>
      <div className="mb-8 flex items-center justify-between">
        <h1 className="text-15px font-medium text-dark dark:text-light">
          {t('profile-sidebar-my-cards')}
        </h1>
        <button
          onClick={handleAddNewCard}
          className="transition-fill-colors flex items-center justify-center gap-2 text-sm font-semibold text-brand duration-200 hover:text-brand-dark"
        >
          <PlusIcon width={13} height={13} />
          {t('profile-add-cards')}
        </button>
      </div>
    </>
  );
};

export default CardViewHeader;
