import { serverSideTranslations } from 'next-i18next/serverSideTranslations';
import type { GetStaticProps } from 'next';
import { useTranslation } from 'next-i18next';
import ErrorMessage from '@/components/ui/error-message';
import Button from '@/components/ui/button';
import ItemNotFound from '@/components/ui/item-not-found';
import type { Question } from '@/types';
import rangeMap from '@/lib/range-map';
import { useMyQuestions } from '@/data/question';
import dayjs from 'dayjs';
import { LikeIcon } from '@/components/icons/like-icon';
import { DislikeIcon } from '@/components/icons/dislike-icon';
import Link from '@/components/ui/link';
import Image from 'next/image';
import routes from '@/config/routes';
import productPlaceholder from '@/assets/images/placeholders/product.svg';
import usePrice from '@/lib/hooks/use-price';
import { NextPageWithLayout } from '@/types';
import DashboardLayout from '@/layouts/_dashboard';

function QuestionItem({ question }: { question: Question }) {
  const {
    question: myQuestion,
    answer,
    created_at,
    positive_feedbacks_count,
    negative_feedbacks_count,
    product,
  } = question;

  const { price, basePrice } = usePrice({
    amount: product?.sale_price ? product?.sale_price : product?.price,
    baseAmount: product?.price,
  });
  const { t } = useTranslation('common');
  return (
    <div className="border-t border-light-400 border-opacity-70 py-7 first:border-t-0 dark:border-dark-400">
      <div className="mb-5 flex space-x-4 rtl:space-x-reverse">
        <div className="relative h-16 w-24 overflow-hidden">
          <Image
            src={product.image?.original ?? productPlaceholder}
            fill
            alt={product?.name}
          />
        </div>
        <div className="flex flex-col">
          <Link
            className="font-medium text-dark dark:text-light sm:mb-1.5"
            href={routes.productUrl(product?.slug)}
          >
            {product?.name}
          </Link>

          <span className="min-w-150 mt-1 flex items-center">
            <ins className="rounded-full bg-light-500 px-1.5 py-1 text-13px font-semibold uppercase text-brand no-underline dark:bg-dark-500 dark:text-brand-dark ">
              {price}
            </ins>
            {basePrice && (
              <del className="text-muted ml-2 text-sm font-normal ltr:ml-3 rtl:mr-3">
                {basePrice}
              </del>
            )}
          </span>
        </div>
      </div>

      <div className="rounded-md bg-light-200 px-4 py-4 dark:bg-dark-350">
        <p className=" mb-2.5 text-13px text-dark dark:text-light">
          <span
            className="inline-block uppercase ltr:mr-2 rtl:ml-2"
            title="Question"
          >
            {t('text-q')}:
          </span>
          {myQuestion}
        </p>
        {answer && (
          <p className="text-13px">
            <span
              className="inline-block text-13px text-dark ltr:mr-2 rtl:ml-2 dark:text-light"
              title="Answer"
            >
              {t('text-a')}:
            </span>
            <span className="text-dark dark:text-light">{answer}</span>
          </p>
        )}

        <div className="mt-5 flex items-center ">
          <div className="text-xs text-gray-400 dark:text-dark-850 ">
            {t('text-date')}: {dayjs(created_at).format('D MMMM, YYYY')}
          </div>

          <div className="flex items-center space-x-6 ltr:ml-6 rtl:mr-6 rtl:space-x-reverse">
            <span className="flex items-center text-xs tracking-wider text-gray-400 selection:transition dark:text-dark-850">
              <LikeIcon className="-mt-1 h-4 w-4 ltr:mr-1.5 rtl:ml-1.5" />
              <span className="ml-2 ">{positive_feedbacks_count}</span>
            </span>
            <span className="flex items-center text-xs tracking-wider text-gray-400 transition dark:text-dark-850">
              <DislikeIcon className="mt-1 h-4 w-4 ltr:mr-1.5 rtl:ml-1.5" />
              <span className="ml-2 ">{negative_feedbacks_count}</span>
            </span>
          </div>
        </div>
      </div>
    </div>
  );
}

const QuestionItemLoader = (props: any) => (
  <div className="flex animate-pulse items-start gap-4 border-b border-light-400 py-4 last:border-b-0 dark:border-dark-400 sm:items-stretch sm:gap-5">
    <div className="relative aspect-[5/3.4] w-28 flex-shrink-0 bg-light-400 dark:bg-dark-400 sm:w-32 md:w-36" />
    <div className="flex flex-1 flex-col gap-4 sm:flex-row sm:items-center sm:justify-between md:gap-0">
      <div className="h-full flex-grow border-b border-light-400 pb-3 dark:border-dark-600 sm:border-b-0 sm:pb-0">
        <div className="mb-3 h-2.5 w-1/4 bg-light-400 dark:bg-dark-400" />
        <div className="mb-6 h-2.5 w-1/6 rounded-full bg-light-400 dark:bg-dark-400" />
      </div>
    </div>
  </div>
);

const LIMIT = 10;

const MyQuestionsPage: NextPageWithLayout = () => {
  const { t } = useTranslation('common');
  const { questions, isLoading, isLoadingMore, error, hasMore, loadMore } =
    useMyQuestions({ limit: LIMIT });

  if (error) return <ErrorMessage message={error.message} />;

  // loader
  if (!questions.length && isLoading) {
    return (
      <div className="flex w-full flex-col">
        <div className="flex items-center ">
          <h1 className="text-15px font-medium text-dark dark:text-light">
            {t('text-my-question-title')}
          </h1>
        </div>
        {isLoading &&
          !questions.length &&
          rangeMap(LIMIT, (i) => <QuestionItemLoader key={`question-${i}`} />)}
      </div>
    );
  }

  if (!questions.length && !isLoading) {
    return (
      <div className="flex w-full flex-col">
        <div className="flex items-center ">
          <h1 className="text-15px font-medium text-dark dark:text-light">
            {t('text-my-question-title')}
          </h1>
        </div>
        <ItemNotFound
          title="No Download"
          className="mx-auto w-full md:w-7/12"
          message="No Download"
        />
      </div>
    );
  }

  return (
    <>
      <div className="flex w-full flex-col">
        <div className="flex items-center ">
          <h1 className="text-15px font-medium text-dark dark:text-light">
            {t('text-my-question-title')}
          </h1>
        </div>
        <div>
          {questions?.map((item) => (
            <QuestionItem key={item.id} question={item} />
          ))}
        </div>
      </div>

      {hasMore && (
        <div className="mt-8 flex w-full justify-center">
          <Button
            isLoading={isLoadingMore}
            disabled={isLoadingMore}
            onClick={loadMore}
          >
            {t('text-loadmore')}
          </Button>
        </div>
      )}
    </>
  );
};

MyQuestionsPage.authorization = true;
MyQuestionsPage.getLayout = function getLayout(page) {
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

export default MyQuestionsPage;
