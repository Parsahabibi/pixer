import { serverSideTranslations } from 'next-i18next/serverSideTranslations';
import type { GetStaticProps } from 'next';
import { useTranslation } from 'next-i18next';
import type { NextPageWithLayout, Product } from '@/types';
import { motion } from 'framer-motion';
import DashboardLayout from '@/layouts/_dashboard';
import Image from '@/components/ui/image';
import CartEmpty from '@/components/cart/cart-empty';
import { fadeInBottom } from '@/lib/framer-motion/fade-in-bottom';
import rangeMap from '@/lib/range-map';
import Button from '@/components/ui/button';
import placeholder from '@/assets/images/placeholders/product.svg';
import { useRemoveFromWishlist, useWishlist } from '@/data/wishlist';
import usePrice from '@/lib/hooks/use-price';
import { isFree } from '@/lib/is-free';
import AddToCart from '@/components/cart/add-to-cart';
import FreeDownloadButton from '@/components/product/free-download-button';
import classNames from 'classnames';
import { HeartFillIcon } from '@/components/icons/heart-fill';
import Link from '@/components/ui/link';

function WishlistItem({ product }: { product: Product }) {
  const { removeFromWishlist, isLoading } = useRemoveFromWishlist();
  const {
    id,
    slug,
    name,
    image,
    price: main_price,
    sale_price,
    shop,
  } = product ?? {};

  const { price, basePrice } = usePrice({
    amount: sale_price ? sale_price : main_price,
    baseAmount: main_price,
  });
  const productSingleUrl =
    product?.language !== process.env.NEXT_PUBLIC_DEFAULT_LANGUAGE
      ? `${product?.language}/products/${product?.slug}`
      : `/products/${product?.slug}`;

  const isFreeItem = isFree(product?.sale_price ?? product?.price);

  return (
    <div className="flex items-start gap-4 border-b border-light-400 py-4 last:border-b-0 dark:border-dark-400 sm:gap-5">
      <div className="relative aspect-[5/3.4] w-28 flex-shrink-0 border border-light-300 dark:border-0 sm:w-32 md:w-36">
        <Image
          alt={name}
          fill
          quality={100}
          src={image?.thumbnail ?? placeholder}
          className="bg-light-400 object-cover dark:bg-dark-400"
        />
      </div>
      <div className="sm:flex-start flex flex-1 flex-col gap-3 sm:flex-row sm:justify-between md:gap-0">
        <div className="border-b border-light-400 pb-4 dark:border-dark-600 sm:border-b-0 sm:pb-0">
          <Link
            href={`${productSingleUrl}`}
            className="font-medium text-dark dark:text-light sm:mb-1.5"
            locale={product?.language}
          >
            {name}
          </Link>
          <p className="pt-0.5 font-medium text-gray-500 dark:text-gray-400 sm:pt-0">
            {shop?.name}
          </p>
          <div className="mt-2 sm:mt-3">
            <span className="rounded-full bg-light-500 px-1.5 py-1 text-13px font-semibold uppercase text-brand dark:bg-dark-500 dark:text-brand-dark">
              {isFreeItem ? 'Free' : price}
            </span>
            {!isFreeItem && basePrice && (
              <del className="ml-2 px-1 text-13px font-medium text-dark-900 dark:text-dark-700">
                {basePrice}
              </del>
            )}
          </div>
        </div>

        <div className="flex shrink-0 items-center gap-2.5 xs:pb-4 xs:pt-8 md:flex-nowrap md:gap-3.5 lg:gap-4">
          {!isFreeItem ? (
            <AddToCart
              item={product}
              withPrice={false}
              toastClassName="-mt-10 xs:mt-0"
              className="w-full flex-1 shrink-0 rounded border border-light-200 bg-brand text-brand hover:bg-transparent hover:text-light-200 dark:border-dark-600 dark:bg-dark-250 dark:text-brand dark:hover:text-brand-dark"
            />
          ) : (
            <FreeDownloadButton
              productId={id}
              productSlug={slug}
              productName={name}
              className="w-full flex-1 text-brand"
            />
          )}

          <button
            type="button"
            className={classNames(
              'flex min-h-[46px] w-12 shrink-0 items-center justify-center rounded border border-brand  transition-colors hover:bg-transparent hover:text-light-200 dark:border-dark-600 sm:h-12',
              {
                '!border-accent': true,
              }
            )}
            disabled={isLoading}
            onClick={() => {
              removeFromWishlist(product?.id);
            }}
          >
            <HeartFillIcon className="text-accent h-5 w-5 text-brand dark:text-brand dark:hover:text-brand-dark " />
          </button>
        </div>
      </div>
    </div>
  );
}

function WishlistItemLoader(props: any) {
  return (
    <div className="flex animate-pulse items-start gap-4 border-b border-light-400 py-4 last:border-b-0 dark:border-dark-400 sm:items-stretch sm:gap-5">
      <div className="relative aspect-[5/3.4] w-28 flex-shrink-0 bg-light-400 dark:bg-dark-400 sm:w-32 md:w-36" />
      <div className="flex flex-1 flex-col gap-4 sm:flex-row sm:items-center sm:justify-between md:gap-0">
        <div className="h-full flex-grow border-b border-light-400 pb-3 dark:border-dark-600 sm:border-b-0 sm:pb-0">
          <div className="mb-3 h-2.5 w-1/4 bg-light-400 dark:bg-dark-400" />
          <div className="mb-6 h-2.5 w-2/4 bg-light-400 dark:bg-dark-400" />
          <div className="h-2.5 w-1/5 bg-light-400 dark:bg-dark-400" />
        </div>
        <div className="h-2.5  bg-light-400 dark:bg-dark-400 sm:h-12  sm:w-28 sm:rounded " />
        <div className="ml-3 h-2.5 w-4 bg-light-400 dark:bg-dark-400 sm:h-12 sm:w-12 sm:rounded" />
      </div>
    </div>
  );
}

const LIMIT = 10;
const MyWishlistPage: NextPageWithLayout = () => {
  const { t } = useTranslation('common');
  const { wishlists, isLoading, isLoadingMore, loadMore, hasNextPage } =
    useWishlist();

  return (
    <motion.div
      variants={fadeInBottom()}
      className="flex min-h-full flex-grow flex-col"
    >
      <h1 className="mb-3 text-15px font-medium text-dark dark:text-light">
        {t('text-wishlist-title')}
        <span className="ml-1 text-light-900">({wishlists.length})</span>
      </h1>

      {isLoading &&
        !wishlists.length &&
        rangeMap(LIMIT, (i) => (
          <WishlistItemLoader key={`order-loader-${i}`} />
        ))}

      {!isLoading && !wishlists.length ? (
        <CartEmpty
          className="my-auto"
          description={t('text-product-purchase-message')}
        />
      ) : (
        wishlists.map((product) => (
          <WishlistItem key={product.id} product={product} />
        ))
      )}

      {hasNextPage && (
        <div className="mt-10 grid place-content-center">
          <Button
            onClick={loadMore}
            disabled={isLoadingMore}
            isLoading={isLoadingMore}
          >
            {t('text-loadmore')}
          </Button>
        </div>
      )}
    </motion.div>
  );
};

MyWishlistPage.authorization = true;
MyWishlistPage.getLayout = function getLayout(page) {
  return <DashboardLayout>{page}</DashboardLayout>;
};

export const getStaticProps: GetStaticProps = async ({ locale }) => {
  return {
    props: {
      ...(await serverSideTranslations(locale!, ['common'])),
    },
    revalidate: 60, // In seconds
  };
};

export default MyWishlistPage;
