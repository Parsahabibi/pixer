import { Review } from '@/types';

/**
 * Helper method for find out reviews
 *
 * @param reviews
 * @param orderId
 */
export function getReview(reviews: Review[], orderId: string) {
  return reviews?.find((review: Review) => review?.order_id === orderId);
}
