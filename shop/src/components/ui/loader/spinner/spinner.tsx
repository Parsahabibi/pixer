import styles from './spinner.module.css';
import cn from 'classnames';
import { SpinnerIcon } from '@/components/icons/spinner-icon';
import { useTranslation } from 'next-i18next';

interface Props {
  className?: string;
  text?: string;
  showText?: boolean;
  simple?: boolean;
}

const Spinner = (props: Props) => {
  const { className, showText = true, text = 'Loading', simple } = props;
  return (
    <>
      {simple ? (
        <span className={cn(className, styles.simple_loading)} />
      ) : (
        <span
          className={cn(
            'flex h-screen w-full flex-col items-center justify-center',
            className
          )}
        >
          <span className={styles.loading} />

          {showText && (
            <h3 className="text-body text-lg font-semibold italic">{text}</h3>
          )}
        </span>
      )}
    </>
  );
};

interface SpinnerPops {
  className?: string;
}

export const SpinnerLoader = (props: SpinnerPops) => {
  const { className } = props;
  return (
    <span
      className={cn(
        'border-t-accent inline-flex h-5 w-5 animate-spin rounded-full border-2 border-t-2 border-transparent',
        className
      )}
    />
  );
};

interface PageLoaderProps {
  className?: string;
  text?: string;
  showText?: boolean;
}

export const PageLoader = (props: PageLoaderProps) => {
  const { t } = useTranslation('common');
  const { className, showText = true, text = t('text-loader-title') } = props;
  return (
    <div className={cn('flex w-full flex-grow text-lg', className)}>
      <SpinnerIcon className="m-auto h-auto w-6 animate-spin" />{' '}
      {showText && <span className="ml-1">{text}</span>}
    </div>
  );
};

export default Spinner;
