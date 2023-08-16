import RatingsBadge from './rating-badge';
import RatingProgressBar from './rating-progress-bar';
import { RatingCount } from '@/types';
import { useTranslation } from 'next-i18next';

type AverageRatingsProps = {
  totalReviews?: number;
  ratings?: number;
  ratingCount?: RatingCount[];
};

const AverageRatings: React.FC<AverageRatingsProps> = ({
  totalReviews,
  ratings,
  ratingCount,
}) => {
  const { t } = useTranslation('common');
  if (!ratingCount) return null;
  return (
    <div className="flex w-[calc(100%+32px)] flex-col divide-y divide-light-500 border-t border-b border-light-500 px-4 py-6 ltr:-ml-4 rtl:-mr-4 dark:divide-dark-400 dark:border-dark-400 sm:flex-row sm:items-center sm:space-x-7 sm:divide-y-0 sm:divide-x sm:py-7 md:w-full md:space-x-12 md:border-t-0 md:border-b-0 md:py-0 md:px-0 ltr:md:ml-0 rtl:md:mr-0">
      <div className="w-full pb-4 sm:w-auto sm:pb-0">
        <RatingsBadge rating={ratings} className="mb-5" variant="large" />
        <p className="text-13px dark:text-light-600">
          <span>
            {totalReviews} {t('text-reviews')}
          </span>
        </p>
      </div>
      <div className="w-full space-y-3 py-0.5 pt-4 sm:w-auto sm:px-6 sm:pt-0 md:px-8">
        <RatingProgressBar
          ratingProgressItem={ratingCount.find(
            (rating) => Number(rating.rating) === 5
          )}
          ratingId={5}
          totalReviews={totalReviews!}
        />
        <RatingProgressBar
          ratingProgressItem={ratingCount.find(
            (rating) => Number(rating.rating) === 4
          )}
          ratingId={4}
          totalReviews={totalReviews!}
        />
        <RatingProgressBar
          ratingProgressItem={ratingCount.find(
            (rating) => Number(rating.rating) === 3
          )}
          ratingId={3}
          totalReviews={totalReviews!}
        />
        <RatingProgressBar
          ratingProgressItem={ratingCount.find(
            (rating) => Number(rating.rating) === 2
          )}
          ratingId={2}
          totalReviews={totalReviews!}
        />
        <RatingProgressBar
          ratingProgressItem={ratingCount.find(
            (rating) => Number(rating.rating) === 1
          )}
          ratingId={1}
          totalReviews={totalReviews!}
        />
      </div>
    </div>
  );
};

export default AverageRatings;
