import { forwardRef } from 'react';
import classNames from 'classnames';
import { useTranslation } from 'next-i18next';

type InputProps = React.DetailedHTMLProps<
  React.InputHTMLAttributes<HTMLInputElement>,
  HTMLInputElement
> & {
  label: string;
  error?: string;
  className?: string;
  inputClassName?: string;
};

const Input = forwardRef<HTMLInputElement, InputProps>(
  (
    {
      label,
      error,
      type = 'text',
      className,
      inputClassName = 'bg-transparent',
      ...props
    },
    ref
  ) => {
    const { t } = useTranslation('common');
    return (
      <div className={className}>
        <label className="block text-13px">
          {label && (
            <span className="block cursor-pointer pb-2.5 font-normal text-dark/70 rtl:text-right dark:text-light/70">
              {t(label)}
            </span>
          )}
          <input
            type={type}
            ref={ref}
            {...props}
            className={classNames(
              'h-11 w-full appearance-none rounded border border-light-500 bg-transparent px-4 py-2 text-13px text-dark ring-[0.5px] ring-light-500 placeholder:text-dark-900 focus:border-brand focus:ring-[0.5px] focus:ring-brand dark:border-dark-600 dark:text-light dark:ring-dark-600 dark:placeholder:text-dark-700 dark:focus:border-brand dark:focus:ring-brand md:h-12 lg:px-5 xl:h-[50px]',
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

Input.displayName = 'Input';
export default Input;
