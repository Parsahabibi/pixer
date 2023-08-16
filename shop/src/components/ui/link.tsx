import classNames from 'classnames';
import NextLink, { LinkProps as NextLinkProps } from 'next/link';

export interface LinkProps extends NextLinkProps {
  className?: string;
  title?: string;
  target?: string;
  variant?: 'button';
  children?: React.ReactNode;
}

const Link: React.FC<LinkProps> = ({
  href,
  variant,
  title,
  target,
  className,
  locale,
  ...props
}) => {
  return (
    <NextLink
      href={href}
      locale={locale}
      className={classNames(
        {
          "bg-accent hover:bg-accent-hover focus:ring-accent-700' inline-flex h-9 flex-shrink-0 items-center justify-center rounded border border-transparent px-3 py-0 text-sm font-semibold leading-none text-light outline-none transition duration-300 ease-in-out focus:shadow focus:outline-none focus:ring-1":
            variant === 'button',
        },
        className
      )}
      title={title}
      {...props}
    />
  );
};

export default Link;
