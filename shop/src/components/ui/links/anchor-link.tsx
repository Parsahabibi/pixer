import type { LinkProps } from 'next/link';
import NextLink from 'next/link';

const AnchorLink: React.FC<
  LinkProps &
    Omit<React.AnchorHTMLAttributes<HTMLAnchorElement>, 'href'> & {
      children?: React.ReactNode;
    }
> = ({ href, ...props }) => {
  return <NextLink href={href} {...props} />;
};

export default AnchorLink;
