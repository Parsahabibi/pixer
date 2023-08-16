import ReviewCard from '@/components/review/review-card';
import Pagination from '@/components/ui/pagination';
import { useState } from 'react';
import Sorting from './sorting';
import { useRouter } from 'next/router';
import { useReviews } from '@/data/review';
import { useTranslation } from 'next-i18next';

type ProductReviewsProps = {
  className?: any;
  productId: string;
  productType?: string;
};

const ProductReviews: React.FC<ProductReviewsProps> = ({ productId }) => {
  const { query } = useRouter();
  const { text, ...restQuery } = query;
  const [page, setPage] = useState(1);

  const { reviews, paginatorInfo } = useReviews({
    product_id: productId,
    limit: 5,
    page,
    ...restQuery,
  });

  function onPagination(current: number) {
    setPage(current);
  }
  const { t } = useTranslation('common');
  return (
    <div className="block">
      <div className="flex w-[calc(100%+32px)] flex-col justify-between border-b border-light-500 px-4 py-5 ltr:-ml-4 rtl:-mr-4 dark:border-dark-400 sm:flex-row sm:items-center sm:py-4 md:w-full md:px-0 ltr:md:ml-0 rtl:md:mr-0">
        <h2 className="text-sm tracking-tight text-dark dark:text-light">
          {t('text-product-reviews')} ({paginatorInfo?.total ?? 0})
        </h2>
        <div className="flex items-center pt-2.5 sm:pt-0">
          <span className="mr-2 sm:hidden">Sort By :</span>
          <Sorting />
        </div>
      </div>

      {reviews?.length !== 0 ? (
        <div className="block">
          <div className="block">
            {reviews?.map((review: any) => (
              <ReviewCard key={`review-no-${review?.id}`} review={review} />
            ))}
          </div>

          {/* Pagination */}
          {paginatorInfo && (
            <div className="flex flex-col items-center justify-between space-y-1 border-t border-light-500 py-5 dark:border-dark-400 md:flex-row md:space-y-0 md:py-3">
              <div className="text-13px text-dark-700 dark:text-light-900 md:mt-2">
                {t('text-page')} {paginatorInfo.currentPage} {t('text-of')}{' '}
                {Math.ceil(paginatorInfo.total / paginatorInfo.perPage)}
              </div>

              <Pagination
                total={paginatorInfo.total}
                current={paginatorInfo.currentPage}
                pageSize={paginatorInfo.perPage}
                onChange={onPagination}
                showTitle={false}
              />
            </div>
          )}
        </div>
      ) : (
        <div className="flex flex-col items-center justify-center border-b border-light-500 px-5 py-16 dark:border-dark-400">
          <h3 className="text-lg font-semibold text-dark-600 dark:text-light-600">
            {t('text-no-reviews-found')}
          </h3>
        </div>
      )}
    </div>
  );
};

export default ProductReviews;
