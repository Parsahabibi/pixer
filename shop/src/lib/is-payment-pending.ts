import { OrderStatus, PaymentGateway, PaymentStatus } from '@/types';

/**
 * Utility method to find out is payment action pending or not
 *
 * @param paymentGateway
 * @param orderStatus
 * @param paymentStatus
 */
export function isPaymentPending(
  paymentGateway: PaymentGateway,
  orderStatus: OrderStatus,
  paymentStatus: PaymentStatus
) {
  const isPaymentWallet = ![PaymentGateway.FULL_WALLET_PAYMENT].includes(
    paymentGateway
  );
  const isOrderPending = ![OrderStatus.CANCELLED].includes(orderStatus);
  return (
    isPaymentWallet && isOrderPending && paymentStatus !== PaymentStatus.SUCCESS
  );
}
