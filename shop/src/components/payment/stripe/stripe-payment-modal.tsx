import { Card, PaymentGateway, PaymentIntentInfo } from '@/types';
import { Elements } from '@stripe/react-stripe-js';
import getStripe from '@/lib/get-stripejs';
import StripePaymentForm from '@/components/payment/stripe/stripe-payment-form';
import StripeSavedCardsList from '@/components/payment/stripe/stripe-saved-cards-list';
import { useTranslation } from 'next-i18next';

interface Props {
  paymentIntentInfo: PaymentIntentInfo;
  trackingNumber: string;
  paymentGateway: PaymentGateway;
  cards: Card[];
}

const StripePaymentModal: React.FC<Props> = ({
  paymentIntentInfo,
  trackingNumber,
  paymentGateway,
  cards,
}) => {
  const { t } = useTranslation('common');

  return (
    <Elements stripe={getStripe()}>
      {cards?.length > 0 ? (
        <div className="flex h-full min-h-screen w-screen flex-col justify-center bg-light text-left dark:bg-dark-250 xs:h-auto xs:min-h-0 xs:max-w-[400px] md:max-w-[590px] md:rounded-xl lg:max-w-[750px]">
          <h3 className="mb-2 pl-6 pr-6 pt-8 text-center text-base font-medium tracking-[-0.3px] text-dark dark:text-light xs:mb-0 xs:border-b xs:border-dark/10 xs:py-4 xs:pr-10 xs:text-left xs:dark:border-light/10 md:py-5 md:pl-8 lg:py-6 lg:pr-16">
            {t('profile-sidebar-my-cards')}
          </h3>
          <div className="p-6 md:p-8">
            <StripeSavedCardsList view="modal" payments={cards} />
          </div>
        </div>
      ) : (
        <StripePaymentForm
          paymentIntentInfo={paymentIntentInfo}
          trackingNumber={trackingNumber}
          paymentGateway={paymentGateway}
        />
      )}
    </Elements>
  );
};

export default StripePaymentModal;
