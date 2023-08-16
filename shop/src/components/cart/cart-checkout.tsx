import { useState, useEffect } from 'react';
import { useRouter } from 'next/router';
import { useMutation } from 'react-query';
import { useAtom } from 'jotai';
import toast from 'react-hot-toast';
import client from '@/data/client';
import usePrice from '@/lib/hooks/use-price';
import Button from '@/components/ui/button';
import { useCart } from '@/components/cart/lib/cart.context';
import {
  calculatePaidTotal,
  calculateTotal,
} from '@/components/cart/lib/cart.utils';
import CartWallet from '@/components/cart/cart-wallet';
import { usePhoneInput } from '@/components/ui/forms/phone-input';
import {
  payableAmountAtom,
  useWalletPointsAtom,
  verifiedTokenAtom,
  checkoutAtom,
} from '@/components/cart/lib/checkout';
import StripePayment from '@/components/cart/payment/stripe';
import PaymentGrid from '@/components/cart/payment/payment-grid';
import routes from '@/config/routes';
import { useTranslation } from 'next-i18next';
import { PaymentGateway } from '@/types';
import { useSettings } from '@/data/settings';

export default function CartCheckout() {
  const { settings } = useSettings();
  const router = useRouter();
  const { locale } = router;
  const { t } = useTranslation('common');

  const { mutate, isLoading } = useMutation(client.orders.create, {
    // onSuccess: (res) => {
    //   console.log(res, 'response');
    //   router.push(routes.orderUrl(res.tracking_number));
    // },

    onSuccess: (res) => {
      console.log(res, 'response');
      const { tracking_number, payment_gateway, payment_intent } = res;
      if (tracking_number) {
        if (
          [PaymentGateway.FULL_WALLET_PAYMENT].includes(
            payment_gateway as PaymentGateway
          )
        ) {
          return router.push(`${routes.orderUrl(tracking_number)}/payment`);
        }

        if (payment_intent?.payment_intent_info?.is_redirect) {
          return router.push(
            payment_intent?.payment_intent_info?.redirect_url as string
          );
        } else {
          return router.push(`${routes.orderUrl(tracking_number)}/payment`);
        }
      }
    },

    onError: (err: any) => {
      toast.error(<b>{t('text-profile-page-error-toast')}</b>);
      console.log(err.response.data.message);
    },
  });

  const [{ payment_gateway }] = useAtom(checkoutAtom);
  const [use_wallet_points] = useAtom(useWalletPointsAtom);
  const [payableAmount] = useAtom(payableAmountAtom);
  const [token] = useAtom(verifiedTokenAtom);
  const { items, verifiedResponse } = useCart();

  const available_items = items.filter(
    (item) =>
      !verifiedResponse?.unavailable_products?.includes(item.id.toString())
  );

  // Calculate price
  const { price: tax } = usePrice(
    verifiedResponse && {
      amount: verifiedResponse.total_tax ?? 0,
    }
  );

  const base_amount = calculateTotal(available_items);

  const { price: sub_total } = usePrice(
    verifiedResponse && {
      amount: base_amount,
    }
  );

  const totalPrice = verifiedResponse
    ? calculatePaidTotal(
        {
          totalAmount: base_amount,
          tax: verifiedResponse.total_tax,
          shipping_charge: verifiedResponse.shipping_charge,
        },
        0
      )
    : 0;

  const { price: total } = usePrice(
    verifiedResponse && {
      amount: totalPrice,
    }
  );

  // phone number field
  const { phoneNumber } = usePhoneInput();
  function createOrder() {
    // if (
    //   (use_wallet && Boolean(payableAmount) && !token) ||
    //   (!use_wallet && !token)
    // ) {
    //   toast.error(<b>Please verify payment card</b>, {
    //     className: '-mt-10 xs:mt-0',
    //   });
    //   return;
    // }

    // if (!phoneNumber && settings?.useOtp) {
    //   toast.error(<b>{t('text-enter-phone-number')}</b>);
    //   window.scrollTo({ top: 0, left: 0, behavior: 'smooth' });
    //   return;
    // }

    if (settings?.useOtp) {
      if (!phoneNumber) {
        toast.error(<b>{t('text-enter-phone-number')}</b>);
        window.scrollTo({ top: 0, left: 0, behavior: 'smooth' });
        return;
      }
    }

    mutate({
      amount: base_amount,
      total: totalPrice,
      paid_total: totalPrice,
      products: available_items.map((item) => ({
        product_id: item.id,
        order_quantity: item.quantity,
        unit_price: item.price,
        subtotal: item.price * item.quantity,
      })),
      payment_gateway: use_wallet_points
        ? PaymentGateway.FULL_WALLET_PAYMENT
        : payment_gateway,
      use_wallet_points,
      ...(token && { token }),
      sales_tax: verifiedResponse?.total_tax ?? 0,
      customer_contact: phoneNumber ? phoneNumber : '1',
    });
  }

  return (
    <div className="mt-10 border-t border-light-400 bg-light pt-6 pb-7 dark:border-dark-400 dark:bg-dark-250 sm:bottom-0 sm:mt-12 sm:pt-8 sm:pb-9">
      <div className="mb-6 flex flex-col gap-3 text-dark dark:text-light sm:mb-7">
        <div className="flex justify-between">
          <p>{t('text-subtotal')}</p>
          <strong className="font-semibold">{sub_total}</strong>
        </div>
        <div className="flex justify-between">
          <p>{t('text-tax')}</p>
          <strong className="font-semibold">{tax}</strong>
        </div>
        <div className="mt-4 flex justify-between border-t border-light-400 pt-5 dark:border-dark-400">
          <p>{t('text-total')}</p>
          <strong className="font-semibold">{total}</strong>
        </div>
      </div>

      {verifiedResponse && (
        <CartWallet
          totalPrice={totalPrice}
          walletAmount={verifiedResponse.wallet_amount}
          walletCurrency={verifiedResponse.wallet_currency}
        />
      )}

      {/* {use_wallet_points && !Boolean(payableAmount) ? null : <StripePayment />} */}

      {use_wallet_points && !Boolean(payableAmount) ? null : <PaymentGrid />}

      <Button
        disabled={isLoading}
        isLoading={isLoading}
        onClick={createOrder}
        className="w-full md:h-[50px] md:text-sm"
      >
        {t('text-submit-order')}
      </Button>
    </div>
  );
}
