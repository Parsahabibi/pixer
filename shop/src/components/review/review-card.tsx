import cn from 'classnames';
import Rating from '@/components/review/rating-badge';
import dayjs from 'dayjs';
import Image from '@/components/ui/image';
import { LikeIcon } from '@/components/icons/like-icon';
import { DislikeIcon } from '@/components/icons/dislike-icon';
import placeholder from '@/assets/images/placeholders/product.svg';
import Avatar from 'react-avatar';
import isEmpty from 'lodash/isEmpty';
import { useModalAction } from '@/components/modal-views/context';
import type { Review } from '@/types';
import { useMe } from '@/data/user';
import { useCreateFeedback } from '@/data/product';
import { useTranslation } from 'next-i18next';

type ReviewCardProps = {
  review: Review;
};

export default function ReviewCard({ review }: ReviewCardProps) {
  const { openModal } = useModalAction();
  const { t } = useTranslation('common');
  const { createFeedback } = useCreateFeedback();
  const { isAuthorized } = useMe();

  const {
    id,
    comment,
    rating,
    photos,
    created_at,
    user,
    negative_feedbacks_count,
    positive_feedbacks_count,
    my_feedback,
  } = review;

  function feedback(value: { positive: boolean } | { negative: boolean }) {
    if (!isAuthorized) {
      openModal('LOGIN_VIEW');
      return;
    }
    createFeedback({
      model_id: id,
      model_type: 'Review',
      ...value,
    });
  }

  function openAbuseReportModal() {
    if (!isAuthorized) {
      openModal('LOGIN_VIEW');
      return;
    }
    openModal('ABUSE_REPORT', {
      reviewId: id,
    });
  }

  const handleImageClick = (idx: number) => {
    openModal('REVIEW_IMAGE_POPOVER', {
      images: photos,
      initSlide: idx,
    });
  };

  return (
    <div className="flex w-full items-start space-x-3 border-b border-light-500 py-5 last:border-b-0 dark:border-dark-400 sm:space-x-4 md:py-6">
      <div className="relative inline-flex h-8 w-8 shrink-0 justify-center rounded-full border border-light-400 bg-light-300 dark:border-dark-500 dark:bg-dark-500">
        <Avatar
          size="32"
          round={true}
          name={user.name}
          textSizeRatio={2}
          src={user?.profile?.avatar?.thumbnail}
        />
      </div>

      <div className="flex w-full flex-col">
        <div className="flex items-center space-x-3 sm:space-x-4">
          <span className="text-13px font-semibold capitalize text-dark dark:text-light">
            {user?.name}
          </span>
          <Rating rating={rating} variant="xs" />
        </div>
        <p className="mt-3.5 text-13px leading-[1.85em] text-dark-500 dark:text-light-600">
          {comment}
        </p>

        {photos && !isEmpty(photos) && (
          <div className="flex flex-wrap items-start gap-2.5 pt-3 pb-0.5 md:gap-3.5">
            {photos?.map((photo, idx) => (
              <div
                className="relative aspect-square h-20 w-20 cursor-pointer overflow-hidden rounded-md bg-dark bg-opacity-10 dark:bg-light dark:bg-opacity-5"
                key={photo.id}
                onClick={() => handleImageClick(idx)}
              >
                <Image
                  src={photo.thumbnail ?? placeholder}
                  alt={user.name ?? ''}
                  className="inline-flex object-cover"
                  fill
                />
              </div>
            ))}
          </div>
        )}

        <div className="mt-5 flex list-disc items-center space-x-3 text-13px marker:text-sky-400 sm:space-x-4">
          <div className="flex items-center text-dark-800 after:ml-3 after:inline-block after:h-1 after:w-1 after:rounded-full after:bg-dark-900 dark:text-light-900 after:dark:bg-light-900 sm:after:ml-4">
            <span className="hidden sm:block">
              {dayjs(created_at).format('MMMM D, YYYY')}
            </span>
            <span className="sm:hidden">
              {dayjs(created_at).format('MMM D, YYYY')}
            </span>
          </div>
          <button
            onClick={openAbuseReportModal}
            className="flex items-center capitalize text-dark-800 transition duration-200 after:ml-3 after:inline-block after:h-1 after:w-1 after:rounded-full after:bg-dark-900 hover:text-brand focus:outline-none dark:text-light-900 after:dark:bg-light-900 dark:hover:text-brand sm:ml-4"
          >
            {t('text-report')}
          </button>
          <div className="flex items-center space-x-3 sm:space-x-4">
            <button
              className={cn(
                'flex items-center tracking-wider text-dark-800 transition dark:text-dark-900',
                {
                  'dark:!text-light': my_feedback?.positive,
                }
              )}
              disabled={my_feedback?.positive}
              onClick={() => feedback({ positive: true })}
            >
              <LikeIcon
                className={cn('mr-2 h-3.5 w-3.5', {
                  'text-brand': my_feedback?.positive,
                })}
              />
              {positive_feedbacks_count}
            </button>
            <button
              className={cn(
                'flex items-center tracking-wider text-dark-800 transition dark:text-dark-900',
                {
                  'dark:!text-light': my_feedback?.negative,
                }
              )}
              onClick={() => feedback({ negative: true })}
              disabled={my_feedback?.negative}
            >
              <DislikeIcon
                className={cn('mr-2 mt-0.5 h-3.5 w-3.5', {
                  'text-brand': my_feedback?.negative,
                })}
              />
              {negative_feedbacks_count}
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
