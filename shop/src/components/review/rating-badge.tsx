import { StarIcon } from '@/components/icons/star-icon';
import cn from 'classnames';

type RatingProps = {
  className?: any;
  rating: number | undefined;
  variant?: 'xs' | 'small' | 'large';
};

const RatingsBadge: React.FC<RatingProps> = ({
  className = '',
  rating,
  variant = 'small',
  ...props
}) => {
  return (
    <span
      className={cn(
        'inline-flex shrink-0 items-center rounded-full bg-brand text-white',
        {
          'px-2 py-[2px] text-sm font-semibold': variant === 'xs',
          'px-3 py-0.5 text-base': variant === 'small',
          'px-6 py-2 text-2xl font-semibold': variant === 'large',
        },
        className
      )}
      {...props}
    >
      {rating}
      <StarIcon
        className={cn({
          'ml-1 h-2.5 w-2.5 ': variant === 'xs',
          'ml-1.5 h-3 w-3': variant === 'small',
          'ml-2 h-5 w-5': variant === 'large',
        })}
      />
    </span>
  );
};

export default RatingsBadge;
