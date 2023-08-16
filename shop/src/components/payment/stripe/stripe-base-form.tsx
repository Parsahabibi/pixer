import Button from '@/components/ui/button';
import { useTranslation } from 'next-i18next';
import Label from '@/components/ui/forms/label';
import {
  CardNumberElement,
  CardCvcElement,
  CardExpiryElement,
} from '@stripe/react-stripe-js';
import Input from '@/components/ui/forms/input';
import Checkbox from '@/components/ui/forms/checkbox';
import Alert from '@/components/ui/alert';
import isEmpty from 'lodash/isEmpty';
import { useMe } from '@/data/user';
import cn from 'classnames';
import { useIsDarkMode } from '@/lib/hooks/use-is-dark-mode';
import { useModalAction } from '@/components/modal-views/context';

interface Props {
  handleSubmit: any;
  type: 'checkout' | 'save_card';
  loading: boolean;
  changeSaveCard?: any;
  saveCard?: any;
  changeDefaultCard?: any;
  defaultCard?: any;
  cardError: any;
}

const cardInputStyle =
  'h-11 w-full appearance-none dark:bg-dark-250 rounded border-[1px] bg-white border-light-500 px-4 py-2 text-13px text-dark ring-[0.5px] ring-light-500 focus:border-brand focus:ring-[0.5px] focus:ring-brand dark:border-dark-600 dark:text-light dark:ring-dark-600 dark:focus:border-brand dark:focus:border-[1px] dark:focus:ring-brand! md:h-12 lg:px-5 xl:h-[50px]';

const StripeBaseForm: React.FC<Props> = ({
  handleSubmit,
  type = 'save_card',
  loading = false,
  changeSaveCard,
  saveCard,
  changeDefaultCard,
  defaultCard,
  cardError,
}) => {
  const { t } = useTranslation('common');
  const { closeModal } = useModalAction();
  const { isAuthorized } = useMe();
  const { isDarkMode } = useIsDarkMode();

  const cardInputCSS = {
    base: {
      color: isDarkMode ? '#ffffff' : '#000000',
      fontSize: '13px',
      fontFamily: "'Inter', sans-serif",
      iconColor: isDarkMode ? '#dadada' : '#3e3e3e',
      '::placeholder': {
        color: isDarkMode ? '#6e6e6e' : '#999999',
        fontWeight: 'normal',
      },
    },
  };

  return (
    <div className="payment-modal flex h-full min-h-screen w-screen flex-col justify-center bg-light text-left dark:bg-dark-250 xs:h-auto xs:min-h-0 xs:max-w-[400px] md:max-w-[590px] md:rounded-xl lg:max-w-[736px]">
      <h3 className="mb-2 pl-6 pr-6 pt-8 text-center text-base font-medium tracking-[-0.3px] text-dark dark:text-light xs:mb-0 xs:border-b xs:border-dark/10 xs:py-4 xs:pr-10 xs:text-left xs:dark:border-light/10 md:py-5 md:pl-8 lg:py-6 lg:pr-16">
        {type === 'checkout' ? t('text-payment') : t('profile-add-cards')}
      </h3>
      <div className="p-6 md:p-8">
        {!isEmpty(cardError) ? (
          <Alert className="mb-4" message={cardError} variant="error" />
        ) : (
          ''
        )}

        <form onSubmit={handleSubmit} className="flex flex-col gap-6">
          <Input
            label={t('text-profile-name')}
            name="owner_name"
            placeholder={t('text-profile-name')}
            required
          />
          <div>
            <Label>
              <span className="block cursor-pointer pb-2.5 font-normal text-dark/70 rtl:text-right dark:text-light/70">
                {t('text-card-number')}
              </span>
              <CardNumberElement
                options={{
                  showIcon: true,
                  placeholder: t('text-card-number'),
                  style: cardInputCSS,
                }}
                className={cn(cardInputStyle)}
              />
            </Label>
          </div>

          <div className="flex flex-wrap gap-5 lg:flex-nowrap">
            <Label className="mb-0 max-w-full basis-full lg:max-w-[50%] lg:basis-1/2">
              <span className="block cursor-pointer pb-2.5 font-normal text-dark/70 rtl:text-right dark:text-light/70">
                {t('text-card-expiry')}
              </span>
              <CardExpiryElement
                options={{
                  style: cardInputCSS,
                  placeholder: t('text-expire-date-placeholder'),
                }}
                className={cn(cardInputStyle)}
              />
            </Label>

            <Label className="mb-0 max-w-full basis-full lg:max-w-[50%] lg:basis-1/2">
              <span className="block cursor-pointer pb-2.5 font-normal text-dark/70 rtl:text-right dark:text-light/70">
                {t('text-card-cvc')}
              </span>
              <CardCvcElement
                options={{
                  style: cardInputCSS,
                  placeholder: t('text-card-cvc'),
                }}
                className={cn(cardInputStyle)}
              />
            </Label>
          </div>

          <div className="flex flex-col md:flex-row md:items-center md:justify-between md:space-x-7 md:pt-2.5">
            {isAuthorized && type === 'checkout' && (
              <div className="mt-1 shrink-0 md:mt-2">
                <Checkbox
                  name="save_card"
                  label={t('text-save-card')}
                  className="inline-flex"
                  onChange={changeSaveCard}
                  checked={saveCard}
                />
              </div>
            )}

            {isAuthorized && type === 'save_card' && (
              <div className="mt-1 shrink-0 md:mt-2">
                <Checkbox
                  name="make_default_card"
                  label={t('text-add-default-card')}
                  className="inline-flex"
                  onChange={changeDefaultCard}
                  checked={defaultCard}
                />
              </div>
            )}

            <div className="mt-5 flex space-x-4 md:mt-2 md:items-center md:justify-end">
              <Button
                variant="outline"
                onClick={closeModal}
                className="StripePay px-8 text-sm shadow-none md:px-8"
              >
                {t('text-cancel')}
              </Button>
              <Button
                type="submit"
                isLoading={loading}
                disabled={loading}
                className="StripePay px-8 text-sm shadow-none md:px-8"
              >
                {type === 'checkout' ? t('text-pay-now') : t('text-save')}
              </Button>
            </div>
          </div>
        </form>
      </div>
    </div>
  );
};

export default StripeBaseForm;
