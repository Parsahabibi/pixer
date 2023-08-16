import { forwardRef } from 'react';
import cn from 'classnames';
import { useTranslation } from 'next-i18next';

interface CheckBoxProps extends React.InputHTMLAttributes<HTMLInputElement> {
  label?: string | any;
  className?: string;
}

const CheckBox = forwardRef<HTMLInputElement, CheckBoxProps>(
  ({ label, className, ...rest }, ref) => {
    const { t } = useTranslation('common');
    return (
      <label
        className={cn(
          'group flex cursor-pointer items-center justify-between text-13px transition-all',
          className
        )}
      >
        <input
          type="checkbox"
          className="checkbox-component invisible absolute -z-[1] opacity-0"
          ref={ref}
          {...rest}
        />
        <span />
        <span className="text-dark/70 ltr:ml-2.5 rtl:mr-2.5 dark:text-light/70">
          {t(label)}
        </span>
      </label>
    );
  }
);

CheckBox.displayName = 'CheckBox';
export default CheckBox;
