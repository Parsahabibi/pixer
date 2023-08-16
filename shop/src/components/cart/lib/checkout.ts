import { atom } from 'jotai';
import { atomWithStorage } from 'jotai/utils';
import { CHECKOUT } from '@/lib/constants';
import { PaymentGateway } from '@/types';
interface VerifiedResponse {
  total_tax: number;
  unavailable_products: any[];
  wallet_amount: number;
  wallet_currency: number;
}

interface CheckoutState {
  payment_gateway: PaymentGateway;
  verified_response: VerifiedResponse | null;
  [key: string]: unknown;
}

export const defaultCheckout: CheckoutState = {
  payment_gateway: PaymentGateway.STRIPE,
  verified_response: null,
};

export const checkoutAtom = atomWithStorage(CHECKOUT, defaultCheckout);

export const clearCheckoutAtom = atom(null, (_get, set, _data) => {
  return set(checkoutAtom, defaultCheckout);
});

export const paymentGatewayAtom = atom(
  (get) => get(checkoutAtom).payment_gateway,
  (get, set, data: PaymentGateway) => {
    const prev = get(checkoutAtom);
    return set(checkoutAtom, { ...prev, payment_gateway: data });
  }
);

export const customerContactAtom = atom(
  (get) => get(checkoutAtom).customer_contact,
  (get, set, data: string) => {
    const prev = get(checkoutAtom);
    return set(checkoutAtom, { ...prev, customer_contact: data });
  }
);

export const verifiedResponseAtom = atom(
  (get) => get(checkoutAtom).verified_response,
  (get, set, data: VerifiedResponse | null) => {
    const prev = get(checkoutAtom);
    return set(checkoutAtom, { ...prev, verified_response: data });
  }
);

export const walletAtom = atom(
  (get) => get(checkoutAtom).use_wallet,
  (get, set) => {
    const prev = get(checkoutAtom);
    return set(checkoutAtom, { ...prev, use_wallet: !prev.use_wallet });
  }
);

// export const payableAmountAtom = atom(
//   (get) => get(checkoutAtom).payable_amount,
//   (get, set, data: number) => {
//     const prev = get(checkoutAtom);
//     return set(checkoutAtom, { ...prev, payable_amount: data });
//   }
// );

export const useWalletPointsAtom = atom<boolean>(false);
export const payableAmountAtom = atom<number>(0);
export const verifiedTokenAtom = atom<string | null>(null);
