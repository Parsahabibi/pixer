import { CheckMark } from '@/components/icons/checkmark';
import cn from 'classnames';
import Scrollbar from '@/components/ui/scrollbar';
import styles from './progress-box.module.css';

type ProgressProps = {
  data: any[] | undefined;
  status: string;
  filledIndex: number;
};

const ProgressBox: React.FC<ProgressProps> = ({
  status,
  data,
  filledIndex,
}) => {
  return (
    <div className="flex w-full flex-col items-start">
      {data?.map((item: any, index) => (
        <div
          className={styles.progress_container}
          key={`order-status-${index}`}
        >
          <div
            className={cn(
              styles.progress_wrapper,
              index <= filledIndex
                ? `${styles.checked} dark:text-dark-base`
                : ''
            )}
          >
            <div
              className={`${styles.status_wrapper} border-gray-200 bg-gray-100 dark:border-dark-600 dark:bg-dark-450`}
            >
              {index <= filledIndex ? (
                <div className="h-4 w-3">
                  <CheckMark className="w-full" />
                </div>
              ) : (
                item.serial
              )}
            </div>
            <div className={`${styles.bar} bg-gray-100 dark:bg-dark-450`} />
          </div>

          <div
            className={cn(
              'flex flex-col items-start ltr:ml-4 rtl:mr-4',
              index <= filledIndex
                ? `${styles.checked} text-black dark:text-light`
                : ''
            )}
          >
            {item && (
              <span className="text-13px font-normal">{item?.name}</span>
            )}
          </div>
        </div>
      ))}
    </div>
  );
};

export default ProgressBox;
