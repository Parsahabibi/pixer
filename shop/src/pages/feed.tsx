import { serverSideTranslations } from 'next-i18next/serverSideTranslations';
import type { GetStaticProps } from 'next';
import type {
  FollowedShopsQueryOptions,
  NextPageWithLayout,
  SettingsQueryOptions,
} from '@/types';
import Layout from '@/layouts/_layout';
import { useFollowedShopsProducts } from '@/data/shop';
import Grid from '@/components/product/grid';
import Seo from '@/layouts/_seo';
import routes from '@/config/routes';
import { useTranslation } from 'next-i18next';
import { dehydrate, QueryClient } from 'react-query';
import { API_ENDPOINTS } from '@/data/client/endpoints';
import client from '@/data/client';

export const getStaticProps: GetStaticProps = async ({ locale }) => {
  const queryClient = new QueryClient();
  try {
    await Promise.all([
      queryClient.prefetchQuery(
        [API_ENDPOINTS.SETTINGS, { language: locale }],
        ({ queryKey }) =>
          client.settings.all(queryKey[1] as SettingsQueryOptions)
      ),
      queryClient.prefetchInfiniteQuery(
        [API_ENDPOINTS.FOLLOWED_SHOPS_PRODUCTS, { language: locale }],
        ({ queryKey }) =>
          client.products.all(queryKey[1] as FollowedShopsQueryOptions)
      ),
    ]);
    return {
      props: {
        ...(await serverSideTranslations(locale!, ['common'])),
        dehydratedState: JSON.parse(JSON.stringify(dehydrate(queryClient))),
      },
      revalidate: 60, // In seconds
    };
  } catch (error) {
    //* if we get here, the product doesn't exist or something else went wrong
    return {
      notFound: true,
    };
  }
};

function Products() {
  const { products, isLoading } = useFollowedShopsProducts({ limit: 15 });
  const { t } = useTranslation('common');
  return (
    <>
      <div className="flex flex-col-reverse flex-wrap items-center justify-between px-4 pt-5 pb-4 xs:flex-row xs:space-x-4 md:px-6 md:pt-6 lg:px-7 3xl:px-8">
        <div className="pt-3 xs:pt-0">
          {t('text-total')} {products.length} {t('text-product-found')}
        </div>
      </div>
      <Grid
        products={products}
        hasNextPage={false}
        isLoadingMore={false}
        isLoading={isLoading}
      />
    </>
  );
}

const Feed: NextPageWithLayout = () => {
  return (
    <>
      <Seo
        title="Top Products"
        description="Fastest digital download template built with React, NextJS, TypeScript, React-Query and Tailwind CSS."
        url={routes.feed}
      />
      <Products />
    </>
  );
};

Feed.authorization = true;
Feed.getLayout = function getLayout(page) {
  return <Layout>{page}</Layout>;
};

export default Feed;
