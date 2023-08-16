import cn from 'classnames';
import Image from 'next/image';

type AvatarProps = {
  className?: string;
  src: string;
  alt?: string;
  width?: number;
  height?: number;
  onClick?: () => void;
};

const Avatar: React.FC<AvatarProps> = ({
  src,
  className,
  alt = 'Avatar',
  onClick,
  ...rest
}) => {
  return (
    <div
      className={cn(
        'relative h-10 w-10 cursor-pointer overflow-hidden rounded-full border border-border-100',
        className
      )}
      {...rest}
    >
      <Image
        alt={alt}
        src={src}
        fill
        priority={true}
        sizes="(max-width: 768px) 100vw"
      />
    </div>
  );
};

export default Avatar;
