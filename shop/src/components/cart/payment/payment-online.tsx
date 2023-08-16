import { useTranslation } from 'next-i18next';

const PaymentOnline = () => {
  const { t } = useTranslation('common');
  return (
    <span className="text-body block text-sm">{t('text-payment-order')}</span>
  );
};
export default PaymentOnline;
