import {
  useModalAction,
  useModalState,
  MODAL_VIEWS,
} from '@/components/modal-views/context';
import { useCards } from '@/data/card';
import ErrorMessage from '@/components/ui/error-message';
import StripePaymentModal from '@/components/payment/stripe/stripe-payment-modal';
import { PageLoader } from '@/components/ui/loader/spinner/spinner';
import dynamic from 'next/dynamic';

const RazorpayPaymentModal = dynamic(
  () => import('@/components/payment/razorpay/razorpay-payment-modal'),
  { ssr: false }
);

const PAYMENTS_FORM_COMPONENTS: any = {
  STRIPE: {
    component: StripePaymentModal,
    type: 'custom',
  },
  RAZORPAY: {
    component: RazorpayPaymentModal,
    type: 'default',
  },
};

const PaymentModal = () => {
  const {
    data: { paymentGateway, paymentIntentInfo, trackingNumber },
  } = useModalState();
  const { cards, isLoading, error } = useCards();
  const PaymentMethod = PAYMENTS_FORM_COMPONENTS[paymentGateway?.toUpperCase()];
  const PaymentComponent = PaymentMethod?.component;
  const paymentModalType = PaymentMethod?.type;

  if (isLoading) {
    return (
      <div className="h-96 w-screen max-w-md rounded-xl bg-white p-12 dark:bg-dark-250 xs:max-w-[400px] md:max-w-[590px] md:rounded-xl lg:max-w-[736px]">
        <PageLoader showText={false} className="h-full" />
      </div>
    );
  }

  if (error) return <ErrorMessage message={error.message} />;

  return paymentModalType === 'custom' ? (
    <PaymentComponent
      paymentIntentInfo={paymentIntentInfo}
      trackingNumber={trackingNumber}
      paymentGateway={paymentGateway}
      cards={cards}
    />
  ) : (
    <PaymentComponent
      paymentIntentInfo={paymentIntentInfo}
      trackingNumber={trackingNumber}
      paymentGateway={paymentGateway}
      cards={cards}
    />
  );
};
export default PaymentModal;
