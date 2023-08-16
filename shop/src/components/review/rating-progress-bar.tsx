import { StarIcon } from '@/components/icons/star-icon';
import cn from 'classnames';

type RatingProgressProps = {
  ratingId?: number;
  ratingProgressItem: any;
  totalReviews: number;
  colorClassName?: string;
};

export default function RatingProgressBar({
  ratingId = 0,
  ratingProgressItem,
  totalReviews,
  colorClassName = 'bg-brand',
}: RatingProgressProps) {
  return (
    <div className="flex items-center text-sm text-dark-500 dark:text-light-600">
      <div className="flex w-9 shrink-0 items-center space-x-1 font-semibold rtl:space-x-reverse md:w-11">
        <span className="min-w-[10px] text-sm font-semibold">{ratingId}</span>{' '}
        <StarIcon className="ml-1.5 h-2.5 w-2.5" />
      </div>
      <div className="relative h-[5px] w-52 overflow-hidden rounded-md bg-dark bg-opacity-20 dark:bg-light dark:bg-opacity-20 sm:w-40 md:w-52">
        <div
          className={cn('absolute h-full rounded-md', colorClassName)}
          style={{
            width: `${(ratingProgressItem?.total / totalReviews) * 100}%`,
          }}
        />
      </div>
      <div className="shrink-0 ltr:pl-5 rtl:pr-5">
        {ratingProgressItem?.total ?? 0}
      </div>
    </div>
  );
}
