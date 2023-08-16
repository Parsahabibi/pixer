import cn from 'classnames';
import { useTranslation } from 'next-i18next';

export default function Copyright({ className }: { className?: string }) {
  const currentYear = new Date().getFullYear();
  const { t } = useTranslation('common');
  return (
    <div className={cn('tracking-[0.2px]', className)}>
      &copy; {t('text-copy-right')} {currentYear} {t('text-copy-right-by')}{' '}
      <a
        href="https://redq.io"
        target="_blank"
        rel="noopener noreferrer"
        className="hover:text-brand-dark"
      >
        RedQ, Inc
      </a>
      .
    </div>
  );
}
