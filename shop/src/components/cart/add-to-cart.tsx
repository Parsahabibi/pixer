import { useState } from 'react';
import cn from 'classnames';
import toast from 'react-hot-toast';
import Button from '@/components/ui/button';
import { useCart } from '@/components/cart/lib/cart.context';
import usePrice from '@/lib/hooks/use-price';
import type { Product } from '@/types';
import { generateCartItem } from './lib/generate-cart-item';
import { useTranslation } from 'next-i18next';

interface Props {
  item: Product;
  className?: string;
  toastClassName?: string;
  withPrice?: boolean;
  variant?: 'outline' | 'fill';
}

export default function AddToCart({
  item,
  className,
  toastClassName,
  withPrice = true,
  variant = 'fill',
}: Props) {
  const { t } = useTranslation('common');
  const { addItemToCart, updateCartLanguage, language, isInStock } = useCart();
  const [addToCartLoader, setAddToCartLoader] = useState(false);
  const [cartingSuccess, setCartingSuccess] = useState(false);
  const { price } = usePrice({
    amount: item?.sale_price ? item?.sale_price : item?.price,
    baseAmount: item?.price,
  });

  function handleAddToCart() {
    setAddToCartLoader(true);
    setTimeout(() => {
      setAddToCartLoader(false);
      addSuccessfully();
    }, 650);
  }
  function addSuccessfully() {
    if (item?.language !== language) {
      updateCartLanguage(item?.language);
    }
    setCartingSuccess(true);
    addItemToCart(generateCartItem(item), 1);
    toast.success(<b>{t('text-add-to-cart-message')}</b>, {
      className: toastClassName,
    });
    setTimeout(() => {
      setCartingSuccess(false);
    }, 800);
  }
  return (
    <Button
      onClick={() => handleAddToCart()}
      isLoading={addToCartLoader}
      className={cn(
        'relative',
        cartingSuccess
          ? 'is-carting pointer-events-none cursor-not-allowed'
          : 'pointer-events-auto cursor-pointer',
        className
      )}
      disabled={isInStock(item?.id)}
    >
      {t('text-add-to-cart')} {withPrice && price}
      <svg
        viewBox="0 0 37 37"
        xmlns="http://www.w3.org/2000/svg"
        className="absolute top-auto bottom-auto right-3 h-auto w-5 xs:right-4 xs:w-6"
      >
        <path
          fill="none"
          stroke="currentColor"
          strokeLinejoin="round"
          strokeMiterlimit="10"
          strokeWidth="2.3"
          d="M30.5 6.5h0c6.6 6.6 6.6 17.4 0 24h0c-6.6 6.6-17.4 6.6-24 0h0c-6.6-6.6-6.6-17.4 0-24h0c6.6-6.7 17.4-6.7 24 0z"
          className="circle path"
        />
        <path
          fill="none"
          stroke="currentColor"
          strokeLinejoin="round"
          strokeMiterlimit="10"
          strokeWidth="2.3"
          d="M11.6 20L15.9 24.2 26.4 13.8"
          className="tick path"
        />
      </svg>
    </Button>
  );
}
