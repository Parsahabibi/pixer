import Button from '@/components/ui/button';
import { useTranslation } from 'next-i18next';
import { useGetPaymentIntent } from '@/data/order';
import { CreditCardIcon } from '../icons/credit-card-icon';
import cn from 'classnames';

interface Props {
  tracking_number: string;
  variant?: 'medium' | 'card';
}

const PayNowButton: React.FC<Props> = ({
  tracking_number,
  variant = 'medium',
}) => {
  const { t } = useTranslation();
  const { isLoading, getPaymentIntentQuery } = useGetPaymentIntent({
    tracking_number: tracking_number,
  });

  async function handlePayNow() {
    await getPaymentIntentQuery();
  }

  return (
    <Button
      className={cn('w-full text-13px md:px-3', {
        'min-h-[36px] sm:h-9 ': variant === 'medium',
      })}
      onClick={handlePayNow}
      disabled={isLoading}
      isLoading={isLoading}
    >
      {variant === 'card' && <CreditCardIcon className="h-4 w-4" />}
      {t('text-pay-now')}
    </Button>
  );
};

export default PayNowButton;
