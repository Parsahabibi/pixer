import { useModalState } from '@/components/modal-views/context';
import StripePaymentForm from '@/components/payment/stripe/stripe-payment-form';

const ADD_PAYMENTS_FORM_COMPONENTS: any = {
  STRIPE: {
    component: StripePaymentForm,
  },
};

const AddNewPaymentModal = () => {
  const {
    data: { paymentGateway, paymentIntentInfo, trackingNumber },
  } = useModalState();
  const PaymentMethod =
    ADD_PAYMENTS_FORM_COMPONENTS[paymentGateway?.toUpperCase()];
  const PaymentComponent = PaymentMethod?.component;

  return (
    <div className="payment-modal flex h-full min-h-screen w-screen flex-col justify-center bg-light text-left dark:bg-dark-250 xs:h-auto xs:min-h-0 xs:max-w-[400px] md:max-w-[590px] md:rounded-xl lg:max-w-[736px]">
      <PaymentComponent
        paymentIntentInfo={paymentIntentInfo}
        trackingNumber={trackingNumber}
        paymentGateway={paymentGateway}
      />
    </div>
  );
};

export default AddNewPaymentModal;
