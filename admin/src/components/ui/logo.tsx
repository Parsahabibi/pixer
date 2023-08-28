import Image from 'next/image';
import Link from '@/components/ui/link';
import cn from 'classnames';
import { siteSettings } from '@/settings/site.settings';
import { useSettings } from '@/contexts/settings.context';
import {auto} from "@popperjs/core";

const Logo: React.FC<React.AnchorHTMLAttributes<{}>> = ({
  className,
  ...props
}) => {
  const { logo, siteTitle } = useSettings();
  return (
    <Link
      href={siteSettings.logo.href}
      className={cn('inline-flex', className)}
      {...props}
    >
      <span
        className="relative overflow-hidden flex items-center"
        style={{
          width: siteSettings.logo.width,
          height: siteSettings.logo.height,
        }}
      >
        {/*<Image*/}
        {/*  src={logo?.original ?? siteSettings.logo.url}*/}
        {/*  alt={siteTitle ?? siteSettings.logo.alt}*/}
        {/*  fill*/}
        {/*  sizes="(max-width: 768px) 100vw"*/}
        {/*  className="object-contain"*/}
        {/*  loading="eager"*/}
        {/*/>*/}
        <Image src={'/./image/Logo.png'} alt={''} width={200} height={200}/>
        {/*<Image src={'/./image/lightLogo.png'} alt={''} width={100} height={100} />*/}
      </span>
    </Link>
  );
};

export default Logo;
