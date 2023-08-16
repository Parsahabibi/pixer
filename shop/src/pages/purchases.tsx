import { Fragment } from 'react';
import { serverSideTranslations } from 'next-i18next/serverSideTranslations';
import type { GetStaticProps } from 'next';
import { useTranslation } from 'next-i18next';
import type { NextPageWithLayout, OrderedFile } from '@/types';
import PayNowButton from '@/components/payment/pay-now-button';
import dayjs from 'dayjs';
import { useMutation } from 'react-query';
import { motion } from 'framer-motion';
import DashboardLayout from '@/layouts/_dashboard';
import Image from '@/components/ui/image';
import { Menu } from '@/components/ui/dropdown';
import { Transition } from '@/components/ui/transition';
import { useDownloadableProductOrders } from '@/data/order';
import { DownloadIcon } from '@/components/icons/download-icon';
import client from '@/data/client';
import CartEmpty from '@/components/cart/cart-empty';
import { fadeInBottom } from '@/lib/framer-motion/fade-in-bottom';
import rangeMap from '@/lib/range-map';
import Button from '@/components/ui/button';
import placeholder from '@/assets/images/placeholders/product.svg';
import { useModalAction } from '@/components/modal-views/context';
import { getReview } from '@/lib/get-reviews';
import { PaymentStatus } from '@/types';
import Link from '@/components/ui/link';
import routes from '@/config/routes';
import AnchorLink from '@/components/ui/links/anchor-link';
import { CreditCardIcon } from '@/components/icons/credit-card-icon';

function OrderedItem({ item }: { item: OrderedFile }) {
  const { t } = useTranslation('common');
  const { openModal } = useModalAction();
  const { order_id, tracking_number } = item;
  const {
    id: product_id,
    shop_id,
    name,
    slug,
    image,
    preview_url,
    my_review,
  } = item.file.fileable ?? {};
  const { mutate } = useMutation(client.orders.generateDownloadLink, {
    onSuccess: (data) => {
      function download(fileUrl: string, fileName: string) {
        let a = document.createElement('a');
        a.href = fileUrl;
        a.setAttribute('download', fileName);
        a.click();
      }

      download(data, name);
    },
  });

  function openReviewModal() {
    openModal('REVIEW_RATING', {
      product_id,
      shop_id,
      name,
      image,
      my_review: getReview(my_review, order_id),
      order_id,
    });
  }
  const getStatus =
    item?.order?.payment_status === PaymentStatus.SUCCESS ||
    item?.order?.payment_status === PaymentStatus.WALLET;
  return (
    <div className="flex items-start gap-4 border-b border-light-400 py-4 last:border-b-0 dark:border-dark-400 sm:gap-5">
      <AnchorLink href={routes.productUrl(slug)}>
        <div className="relative aspect-[5/3.4] w-28 flex-shrink-0 border border-light-300 dark:border-0 sm:w-32 md:w-36">
          <Image
            alt={name}
            fill
            quality={100}
            src={image?.thumbnail ?? placeholder}
            className="bg-light-400 object-cover dark:bg-dark-400"
          />
        </div>
      </AnchorLink>
      <div className="flex flex-1 flex-col gap-4 sm:flex-row sm:items-center sm:justify-between md:gap-0">
        <div className="border-b border-light-400 pb-3 dark:border-dark-600 sm:border-b-0 sm:pb-0">
          <p className="text-gray-500 dark:text-gray-400">
            {t('text-purchased-on')}{' '}
            {dayjs(item.updated_at).format('MMM D, YYYY')}
          </p>
          <h3
            className="my-1.5 font-medium text-dark dark:text-light sm:mb-3"
            title={name}
          >
            <AnchorLink
              href={routes.productUrl(slug)}
              className="transition-colors hover:text-brand"
            >
              {name}
            </AnchorLink>
          </h3>
          {preview_url && (
            <a
              href={preview_url}
              target="_blank"
              rel="noreferrer"
              className="font-medium text-brand-dark dark:text-brand"
            >
              {t('text-preview')}
            </a>
          )}
        </div>
        <div className="flex items-center gap-3">
          {getStatus ? (
            <>
              <button
                className="flex items-center font-semibold text-brand hover:text-brand-dark sm:h-12 sm:rounded sm:border sm:border-light-500 sm:bg-transparent sm:py-3 sm:px-5 sm:dark:border-dark-600"
                onClick={openReviewModal}
              >
                {getReview(my_review, order_id)
                  ? t('text-update-review')
                  : t('text-write-review')}
              </button>
              <Button onClick={() => mutate(item.digital_file_id)}>
                <DownloadIcon className="h-auto w-4" />
                {t('text-download')}
              </Button>
            </>
          ) : (
            <PayNowButton tracking_number={tracking_number} variant="card" />
            // <Button onClick={() => mutate(item.digital_file_id)}>
            //   <CreditCardIcon className="w-4 h-4" />
            //   {t('text-pay')}
            // </Button>
          )}
          <div className="relative shrink-0">
            <Menu>
              <Menu.Button className="flex items-center space-x-[3px] font-semibold text-brand hover:text-brand-dark sm:h-12 sm:rounded sm:border sm:border-light-500 sm:py-3 sm:px-4 sm:dark:border-dark-600">
                <span className="inline-flex h-1 w-1 shrink-0 rounded-full bg-dark-700 dark:bg-light-800"></span>
                <span className="inline-flex h-1 w-1 shrink-0 rounded-full bg-dark-700 dark:bg-light-800"></span>
                <span className="inline-flex h-1 w-1 shrink-0 rounded-full bg-dark-700 dark:bg-light-800"></span>
              </Menu.Button>
              <Transition
                as={Fragment}
                enter="transition ease-out duration-100"
                enterFrom="transform opacity-0 scale-95"
                enterTo="transform opacity-100 scale-100"
                leave="transition ease-in duration-75"
                leaveFrom="transform opacity-100 scale-100"
                leaveTo="transform opacity-0 scale-95"
              >
                <Menu.Items className="absolute top-[110%] z-30 mt-4 w-48 rounded-md bg-light py-1.5 text-dark  shadow-dropdown ltr:right-0 ltr:origin-top-right rtl:left-0 rtl:origin-top-left dark:bg-dark-400 dark:text-light md:top-[78%]">
                  <Menu.Item>
                    <Link
                      href={`${routes.orderUrl(item?.tracking_number)}/payment`}
                      className="transition-fill-colors block w-full px-5 py-2.5 font-medium hover:bg-light-400 ltr:text-left rtl:text-right dark:hover:bg-dark-600"
                    >
                      {t('text-order-details')}
                    </Link>
                  </Menu.Item>
                </Menu.Items>
              </Transition>
            </Menu>
          </div>
        </div>
      </div>
    </div>
  );
}

function OrderItemLoader() {
  return (
    <div className="flex animate-pulse items-start gap-4 border-b border-light-400 py-4 last:border-b-0 dark:border-dark-400 sm:items-stretch sm:gap-5">
      <div className="relative aspect-[5/3.4] w-28 flex-shrink-0 bg-light-400 dark:bg-dark-400 sm:w-32 md:w-36" />
      <div className="flex flex-1 flex-col gap-4 sm:flex-row sm:items-center sm:justify-between md:gap-0">
        <div className="h-full flex-grow border-b border-light-400 pb-3 dark:border-dark-600 sm:border-b-0 sm:pb-0">
          <div className="mb-3 h-2.5 w-1/4 bg-light-400 dark:bg-dark-400" />
          <div className="mb-6 h-2.5 w-2/4 bg-light-400 dark:bg-dark-400" />
          <div className="h-2.5 w-1/5 bg-light-400 dark:bg-dark-400" />
        </div>
        <div className="h-2.5 w-1/3 bg-light-400 dark:bg-dark-400 sm:h-12 sm:w-1/4 sm:rounded md:w-1/6" />
      </div>
    </div>
  );
}

const LIMIT = 10;
const Purchases: NextPageWithLayout = () => {
  const { t } = useTranslation('common');
  const { downloadableFiles, isLoading, isLoadingMore, hasNextPage, loadMore } =
    useDownloadableProductOrders({
      limit: LIMIT,
      orderBy: 'updated_at',
      sortedBy: 'desc',
    });

  // const {
  //   downloadableFiles,
  //   error,
  //   loadMore,
  //   isLoading,
  //   isFetching,
  //   isLoadingMore,
  //   hasNextPage,
  // } = useDownloadableProductOrders({
  //   limit: LIMIT,
  //   orderBy: 'updated_at',
  //   sortedBy: 'desc',
  // });

  // console.log(downloadableFiles, 'downloadableFiles');

  return (
    <motion.div
      variants={fadeInBottom()}
      className="flex min-h-full flex-grow flex-col"
    >
      <h1 className="mb-3 text-15px font-medium text-dark dark:text-light">
        {t('text-my-purchase-list')}
        <span className="ml-1 text-light-900">
          ({downloadableFiles.length})
        </span>
      </h1>

      {isLoading &&
        !downloadableFiles.length &&
        rangeMap(LIMIT, (i) => <OrderItemLoader key={`order-loader-${i}`} />)}

      {!isLoading && !downloadableFiles.length ? (
        <CartEmpty
          className="my-auto"
          description={t('text-product-purchase-message')}
        />
      ) : (
        downloadableFiles.map((file) => (
          <OrderedItem key={file.id} item={file} />
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

Purchases.authorization = true;
Purchases.getLayout = function getLayout(page) {
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

export default Purchases;
