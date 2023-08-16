import { forwardRef } from 'react';
import cn from 'classnames';
import { useTranslation } from 'next-i18next';

type TextareaProps = React.DetailedHTMLProps<
  React.TextareaHTMLAttributes<HTMLTextAreaElement>,
  HTMLTextAreaElement
> & {
  label?: string;
  error?: string;
  className?: string;
  inputClassName?: string;
};

const Textarea = forwardRef<HTMLTextAreaElement, TextareaProps>(
  (
    { label, error, className, inputClassName = 'bg-transparent', ...props },
    ref
  ) => {
    const { t } = useTranslation('common');
    return (
      <div className={className}>
        <label className="block text-13px">
          {label && (
            <span className="block cursor-pointer pb-2.5 font-normal text-dark/70 dark:text-light/70">
              {t(label)}
            </span>
          )}
          <textarea
            ref={ref}
            {...props}
            className={cn(
              'min-h-[150px] w-full appearance-none rounded border border-light-500 px-4 py-3 text-13px text-dark ring-[0.5px] ring-light-500 placeholder:text-dark-900 focus:border-brand focus:ring-[0.5px] focus:ring-brand dark:border-dark-600 dark:text-light dark:ring-dark-600 dark:placeholder:text-dark-700 dark:focus:border-brand dark:focus:ring-brand lg:px-5',
              inputClassName
            )}
          />
        </label>
        {error && (
          <span role="alert" className="block pt-2 text-xs text-warning">
            {t(error)}
          </span>
        )}
      </div>
    );
  }
);

Textarea.displayName = 'Textarea';
export default Textarea;
