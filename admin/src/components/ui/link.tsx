import NextLink, { LinkProps as NextLinkProps } from 'next/link';

const Link: React.FC<
  NextLinkProps & {
    className?: string;
    title?: string;
    children?: React.ReactNode;
  }
> = ({ className, children, ...props }) => {
  return (
    <NextLink {...props} className={className}>
      {children}
    </NextLink>
  );
};

export default Link;
