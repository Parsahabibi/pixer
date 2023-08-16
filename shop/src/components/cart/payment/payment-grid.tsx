import { useEffect, useState } from 'react';
import { useTranslation } from 'next-i18next';
import cn from 'classnames';
import { PaymentGateway } from '@/types';
import Image from 'next/image';
import {
  Elements,
  CardElement,
  useStripe,
  useElements,
} from '@stripe/react-stripe-js';
import { useAtom } from 'jotai';
import toast from 'react-hot-toast';
import getStripe from '@/lib/get-stripejs';
import Button from '@/components/ui/button';
import { verifiedTokenAtom } from '@/components/cart/lib/checkout';
import { RadioGroup } from '@/components/ui/radio-group';
import { paymentGatewayAtom } from '@/components/cart/lib/checkout';
import PaymentOnline from '@/components/cart/payment/payment-online';
import { useIsDarkMode } from '@/lib/hooks/use-is-dark-mode';
import { useSettings } from '@/data/settings';
import Alert from '@/components/ui/alert';

interface PaymentMethodInformation {
  name: string;
  value: PaymentGateway;
  icon: string;
  iconDark: string;
  component: React.FunctionComponent;
  width: number;
  height: number;
}

interface PaymentGroupOptionProps {
  payment: PaymentMethodInformation;
  theme?: string;
}

const PaymentGroupOption: React.FC<PaymentGroupOptionProps> = ({
  payment: { name, value, icon, iconDark, width, height },
  theme,
}) => {
  const { isDarkMode } = useIsDarkMode();
  return (
    <RadioGroup.Option value={value} key={value}>
      {({ checked }) => (
        <div
          className={cn(
            'relative flex h-[5.625rem] w-full cursor-pointer items-center justify-center rounded border bg-light-300 py-3 text-center dark:border-[#3A3A3A] dark:bg-[#303030]',
            checked && 'border-brand dark:border-brand-dark'
            // {
            //   'shadow-600 !border-gray-800 bg-light': theme === 'bw' && checked,
            // }
          )}
        >
          {icon || iconDark ? (
            <>
              <Image
                src={isDarkMode ? iconDark : icon}
                alt={name}
                className="h-[30px]"
                width={width}
                height={height}
              />
            </>
          ) : (
            <span className="text-heading text-xs font-semibold">{name}</span>
          )}
        </div>
      )}
    </RadioGroup.Option>
  );
};

const PaymentGrid: React.FC<{ className?: string; theme?: 'bw' }> = ({
  className,
  theme,
}) => {
  const [gateway, setGateway] = useAtom(paymentGatewayAtom);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const { t } = useTranslation('common');
  const { settings, isLoading } = useSettings();
  // FixME
  // @ts-ignore
  const AVAILABLE_PAYMENT_METHODS_MAP: Record<
    PaymentGateway,
    PaymentMethodInformation
  > = {
    STRIPE: {
      name: 'Stripe',
      value: PaymentGateway.STRIPE,
      icon: '/payment-gateways/stripe.png',
      iconDark: '/payment-gateways/stripe.png',
      component: PaymentOnline,
      width: 40,
      height: 28,
    },
    PAYPAL: {
      name: 'Paypal',
      value: PaymentGateway.PAYPAL,
      icon: '/payment-gateways/paypal.png',
      iconDark: '/payment-gateways/paypal.png',
      component: PaymentOnline,
      width: 82,
      height: 21,
    },
    RAZORPAY: {
      name: 'RazorPay',
      value: PaymentGateway.RAZORPAY,
      icon: '/payment-gateways/razorpay.png',
      iconDark: '/payment-gateways/razorpay-dark.png',
      component: PaymentOnline,
      width: 82,
      height: 40,
    },
    MOLLIE: {
      name: 'Mollie',
      value: PaymentGateway.MOLLIE,
      icon: '/payment-gateways/mollie.png',
      iconDark: '/payment-gateways/mollie-dark.png',
      component: PaymentOnline,
      width: 100,
      height: 52,
    },
    PAYSTACK: {
      name: 'Paystack',
      value: PaymentGateway.PAYSTACK,
      icon: '/payment-gateways/paystack.png',
      iconDark: '/payment-gateways/paystack-dark.png',
      component: PaymentOnline,
      width: 100,
      height: 52,
    },
    BITPAY: {
      name: 'Bitpay',
      value: PaymentGateway.BITPAY,
      icon: '/payment-gateways/bitpay.png',
      iconDark: '/payment-gateways/bitpay-dark.png',
      component: PaymentOnline,
      width: 100,
      height: 52,
    },
    COINBASE: {
      name: 'Coinbase',
      value: PaymentGateway.COINBASE,
      icon: '/payment-gateways/coinbase.png',
      iconDark: '/payment-gateways/coinbase-dark.png',
      component: PaymentOnline,
      width: 100,
      height: 52,
    },
  };

  useEffect(() => {
    if (settings?.paymentGateway) {
      setGateway(settings?.paymentGateway?.toUpperCase() as PaymentGateway);
    }
  }, [isLoading, settings?.paymentGateway]);

  const PaymentMethod = AVAILABLE_PAYMENT_METHODS_MAP[gateway];
  const Component = PaymentMethod?.component ?? PaymentOnline;
  return (
    <div className={className}>
      {errorMessage ? (
        <Alert
          message={t(`common:${errorMessage}`)}
          variant="error"
          closeable={true}
          className="mt-5"
          onClose={() => setErrorMessage(null)}
        />
      ) : null}

      <RadioGroup value={gateway} onChange={setGateway}>
        <RadioGroup.Label className="mb-5 block text-13px font-medium dark:text-white">
          {t('text-choose-payment')}
        </RadioGroup.Label>

        <div className="mb-8 grid grid-cols-2 gap-4 md:grid-cols-3">
          {settings?.paymentGateway && (
            <PaymentGroupOption
              theme={theme}
              payment={
                AVAILABLE_PAYMENT_METHODS_MAP[
                  settings?.paymentGateway?.toUpperCase() as PaymentGateway
                ]
              }
            />
          )}
        </div>
      </RadioGroup>
      {/* <div className="mb-5">
        <Component />
      </div> */}
    </div>
  );
};

export default PaymentGrid;
