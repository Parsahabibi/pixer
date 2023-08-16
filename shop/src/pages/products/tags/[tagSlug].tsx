import { serverSideTranslations } from 'next-i18next/serverSideTranslations';
import { useTranslation } from 'next-i18next';
import type { NextPageWithLayout, ProductQueryOptions, Tag } from '@/types';
import type {
  GetStaticPaths,
  GetStaticProps,
  InferGetStaticPropsType,
} from 'next';
import Grid from '@/components/product/grid';
import client from '@/data/client';
import { API_ENDPOINTS } from '@/data/client/endpoints';
import { useProducts } from '@/data/product';
import Layout from '@/layouts/_layout';
import { dehydrate, QueryClient } from 'react-query';
import invariant from 'tiny-invariant';

// This function gets called at build time
type ParsedQueryParams = {
  tagSlug: string;
};

export const getStaticPaths: GetStaticPaths<ParsedQueryParams> = async ({
  locales,
}) => {
  invariant(locales, 'locales is not defined');
  const { data } = await client.tags.all({ limit: 100 });

  const paths = data?.flatMap((tag) =>
    locales?.map((locale) => ({ params: { tagSlug: tag.slug }, locale }))
  );
  return {
    paths,
    fallback: 'blocking',
  };
};

type PageProps = {
  tag: Tag;
};
export const getStaticProps: GetStaticProps<
  PageProps,
  ParsedQueryParams
> = async ({ params, locale }) => {
  const queryClient = new QueryClient();
  const { tagSlug } = params!; //* we know it's required because of getStaticPaths
  try {
    const [tag] = await Promise.all([
      client.tags.get({ slug: tagSlug, language: locale }),
      queryClient.prefetchInfiniteQuery(
        [API_ENDPOINTS.PRODUCTS, { tags: tagSlug, language: locale }],
        ({ queryKey }) =>
          client.products.all(queryKey[1] as ProductQueryOptions)
      ),
    ]);
    return {
      props: {
        tag,
        dehydratedState: JSON.parse(JSON.stringify(dehydrate(queryClient))),
        ...(await serverSideTranslations(locale!, ['common'])),
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
const TagPage: NextPageWithLayout<
  InferGetStaticPropsType<typeof getStaticProps>
> = ({ tag }) => {
  const { t } = useTranslation('common');
  const {
    products,
    paginatorInfo,
    isLoading,
    loadMore,
    hasNextPage,
    isLoadingMore,
  } = useProducts(
    { tags: tag.slug },
    {
      staleTime: Infinity,
    }
  );
  return (
    <>
      <div className="flex flex-col items-center justify-between gap-1.5 px-4 pt-5 xs:flex-row md:px-6 md:pt-6 lg:px-7 3xl:px-8">
        <h2 className="font-medium capitalize text-dark-100 dark:text-light">
          #{tag.name}
        </h2>
        <div>
          {t('text-total')} {paginatorInfo?.total} {t('text-product-found')}
        </div>
      </div>
      <Grid
        products={products}
        onLoadMore={loadMore}
        hasNextPage={hasNextPage}
        isLoadingMore={isLoadingMore}
        isLoading={isLoading}
      />
    </>
  );
};

TagPage.getLayout = function getLayout(page) {
  return <Layout>{page}</Layout>;
};
export default TagPage;
