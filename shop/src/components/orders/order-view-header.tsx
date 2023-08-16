import { useTranslation } from 'next-i18next';
import cn from 'classnames';
import StatusColor from '@/components/orders/status-color';
import Badge from '@/components/ui/badge';
import PayNowButton from '@/components/payment/pay-now-button';
import { isPaymentPending } from '@/lib/is-payment-pending';
import { SpinnerLoader } from '@/components/ui/loader/spinner/spinner';

interface OrderViewHeaderProps {
  order: any;
  wrapperClassName?: string;
  buttonSize?: 'big' | 'medium' | 'small';
  loading?: boolean;
}

export default function OrderViewHeader({
  order,
  wrapperClassName = 'lg:px-8 lg:py-3 p-6',
  buttonSize = 'medium',
  loading = false,
}: OrderViewHeaderProps) {
  const { t } = useTranslation('common');
  const isPaymentActionPending = isPaymentPending(
    order?.payment_gateway,
    order?.order_status,
    order?.payment_status
  );

  return (
    <div className={cn(`bg-[#F7F8FA] dark:bg-[#333333] ${wrapperClassName}`)}>
      <div className="text-heading mb-0 flex flex-col flex-wrap items-center gap-x-8 text-base font-bold sm:flex-row md:flex-nowrap">
        <div
          className={`order-2 flex w-full max-w-full basis-full gap-6 xs:flex-nowrap sm:order-1 sm:gap-8 ${
            order?.children?.length > 0 ? '' : 'justify-between'
          } ${isPaymentActionPending ? '' : 'justify-between'}`}
        >
          <div className="flex flex-wrap items-center">
            <span className="mb-2 block text-xs font-normal dark:text-white xs:text-sm lg:mb-0 lg:inline-block lg:ltr:mr-4 lg:rtl:ml-4">
              {t('text-order-status')} :
            </span>
            <div className="w-full lg:w-auto">
              {loading ? (
                <SpinnerLoader />
              ) : (
                <Badge
                  text={t(order?.order_status)}
                  color={StatusColor(order?.order_status)}
                  className="flex min-h-[1.4375rem] items-center justify-center text-[9px] font-bold uppercase !leading-[1.3em] xs:text-xs lg:px-2"
                />
              )}
            </div>
          </div>
          <div className="flex flex-wrap items-center">
            <span className="mb-2 block text-xs font-normal dark:text-white xs:text-sm lg:mb-0 lg:inline-block lg:ltr:mr-4 lg:rtl:ml-4">
              {t('text-payment-status')} :
            </span>
            <div className="w-full lg:w-auto">
              {loading ? (
                <SpinnerLoader />
              ) : (
                <Badge
                  text={t(order?.payment_status)}
                  color={StatusColor(order?.payment_status)}
                  className="flex min-h-[1.4375rem] items-center justify-center text-[9px] font-bold uppercase !leading-[1.3em] xs:text-xs lg:px-2"
                />
              )}
            </div>
          </div>
        </div>
        {isPaymentActionPending && order?.children?.length > 0 ? (
          <span className="order-2 mt-5 w-full max-w-full shrink-0 basis-full sm:order-1 md:mt-0 md:w-auto md:max-w-none md:basis-auto md:ltr:ml-auto md:rtl:mr-auto">
            <PayNowButton tracking_number={order?.tracking_number} />
          </span>
        ) : null}
      </div>
    </div>
  );
}
