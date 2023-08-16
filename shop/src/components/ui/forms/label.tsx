import cn from 'classnames';

const Label: React.FC<React.LabelHTMLAttributes<HTMLLabelElement>> = ({
  className,
  ...rest
}) => {
  return <label className={cn('block text-13px', className)} {...rest} />;
};

export default Label;
