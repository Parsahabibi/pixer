import { serverSideTranslations } from 'next-i18next/serverSideTranslations';
import type { GetStaticProps } from 'next';
import { useTranslation } from 'next-i18next';
import type { NextPageWithLayout, Shop } from '@/types';
import { motion } from 'framer-motion';
import DashboardLayout from '@/layouts/_dashboard';
import Image from '@/components/ui/image';
import { fadeInBottom } from '@/lib/framer-motion/fade-in-bottom';
import rangeMap from '@/lib/range-map';
import Button from '@/components/ui/button';
import placeholder from '@/assets/images/placeholders/product.svg';
import { useFollowedShops } from '@/data/shop';
import FollowButton from '@/components/follow/follow-button';

function FollowedShop({ shop }: { shop: Shop }) {
  const { name, logo } = shop ?? {};

  return (
    <div className="flex items-center gap-4 border-b border-light-400 py-5 last:border-b-0 dark:border-dark-400 sm:gap-5">
      <div className="relative aspect-square w-16 flex-shrink-0 border border-light-300 dark:border-0">
        <Image
          alt={name}
          fill
          quality={100}
          src={logo?.original ?? placeholder}
          className="rounded-3xl bg-light-400 object-cover dark:bg-dark-400"
        />
      </div>
      <div className="flex flex-1 flex-col items-start sm:flex-row sm:items-center sm:justify-between">
        <div className="pb-2 sm:pb-0">
          <h3 className="my-1.5 font-medium text-dark dark:text-light">
            {name}
          </h3>
        </div>
        <FollowButton shop_id={shop.id} />
      </div>
    </div>
  );
}

function FollowedShopLoader() {
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

const FollowedAuthorsPage: NextPageWithLayout = () => {
  const { shops, loadMore, hasNextPage, isLoadingMore, isLoading } =
    useFollowedShops({ limit: LIMIT });
  const { t } = useTranslation('common');

  return (
    <motion.div
      variants={fadeInBottom()}
      className="flex min-h-full flex-grow flex-col"
    >
      <h1 className="mb-3 text-15px font-medium text-dark dark:text-light">
        {t('text-followed-authors')}
        <span className="ml-1 text-light-900">({shops.length})</span>
      </h1>

      {isLoading &&
        !shops.length &&
        rangeMap(LIMIT, (i) => <FollowedShopLoader key={`shop-loader-${i}`} />)}

      {!isLoading &&
        shops &&
        shops.map((shop) => (
          <FollowedShop shop={shop} key={`shop-${shop.id}`} />
        ))}

      {hasNextPage && (
        <div className="mt-10 grid place-content-center">
          <Button
            onClick={loadMore}
            disabled={isLoadingMore}
            isLoading={isLoadingMore}
          >
            Load more
          </Button>
        </div>
      )}
    </motion.div>
  );
};

FollowedAuthorsPage.authorization = true;
FollowedAuthorsPage.getLayout = function getLayout(page) {
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

export default FollowedAuthorsPage;
