import Image from '@/components/ui/image';
import routes from '@/config/routes';
import { useModalState } from '@/components/modal-views/context';
import AnchorLink from '@/components/ui/links/anchor-link';
import ProductSocialShare from '@/components/product/product-social-share';
import ProductInformation from '@/components/product/product-information';
import { ShoppingCartIcon } from '@/components/icons/shopping-cart-icon';
import ProductThumbnailGallery from '@/components/product/product-thumbnail-gallery';
import AddToCart from '@/components/cart/add-to-cart';
import placeholder from '@/assets/images/placeholders/product.svg';
import { isFree } from '@/lib/is-free';
import FreeDownloadButton from '@/components/product/free-download-button';
import { DownloadIcon } from '@/components/icons/download-icon';
import pluralize from 'pluralize';
import { useProduct } from '@/data/product';
import ProductPopupLoader from '@/components/product/product-popup-loader';
import isEmpty from 'lodash/isEmpty';
import FavoriteButton from '@/components/favorite/favorite-button';
import { useTranslation } from 'next-i18next';

function getPreviews(gallery: any[], image: any) {
  if (!isEmpty(gallery) && Array.isArray(gallery)) return gallery;
  if (!isEmpty(image)) return [image];
  return [{}];
}

export default function ProductPopupDetails() {
  const { data } = useModalState();
  const { t } = useTranslation('common');
  const { product, isLoading } = useProduct(data.slug);
  if (!product && isLoading) return <ProductPopupLoader />;
  if (!product) return <div>{t('text-not-found')}</div>;
  const {
    id,
    name,
    description,
    slug,
    image,
    shop,
    updated_at,
    created_at,
    gallery,
    orders_count,
    total_downloads,
    tags,
    preview_url,
    type,
    price,
    sale_price,
  } = product ?? {};
  const isFreeItem = isFree(sale_price ?? price);
  const previews = getPreviews(gallery, image);
  return (
    <div className="flex max-w-full flex-col bg-light text-left dark:bg-dark-250 xs:max-w-[430px] sm:max-w-[550px] md:max-w-[600px] lg:max-w-[960px] xl:max-w-[1200px] 2xl:max-w-[1266px] 3xl:max-w-[1460px]">
      <div className="-mx-2.5 flex flex-wrap items-center bg-light-300 py-3 ltr:pl-4 ltr:pr-16 rtl:pr-4 rtl:pl-16 dark:bg-dark-100 md:py-4 ltr:md:pl-6 rtl:md:pr-6 lg:-mx-4 lg:py-5 ltr:xl:pl-8 rtl:xl:pr-8">
        <h2
          title={name}
          className="truncate px-2.5 py-1 text-base font-medium text-dark dark:text-light md:text-lg ltr:lg:pl-4 ltr:lg:pr-5 rtl:lg:pr-4 rtl:lg:pl-5 3xl:text-xl"
        >
          <AnchorLink
            href={routes.productUrl(slug)}
            className="transition-colors hover:text-brand"
          >
            {name}
          </AnchorLink>
        </h2>
        <div className="flex flex-shrink-0 items-center px-2.5 py-1">
          <div className="relative flex h-5 w-5 flex-shrink-0 md:h-6 md:w-6">
            <Image
              alt={shop.name}
              fill
              quality={100}
              src={shop.logo.thumbnail ?? placeholder}
              className="rounded-full object-cover"
            />
          </div>
          <h3
            title={name}
            className="text-13px font-medium text-dark-600 ltr:pl-2 rtl:pr-2 dark:text-light-800 ltr:md:pl-2.5 rtl:md:pr-2.5"
          >
            <AnchorLink
              href={routes.shopUrl(shop?.slug)}
              className="hover:text-accent transition-colors"
            >
              {shop?.name}
            </AnchorLink>
          </h3>

          <FavoriteButton productId={product?.id} />
        </div>
      </div>
      <div className="flex flex-col p-4 rtl:space-x-reverse md:p-6 lg:flex-row lg:space-x-7 xl:space-x-8 xl:p-8 3xl:space-x-10">
        <div className="mb-4 w-full shrink-0 items-center justify-center overflow-hidden md:mb-6 lg:mb-auto lg:max-w-[480px] xl:flex xl:max-w-[570px] 2xl:max-w-[650px] 3xl:max-w-[795px]">
          <ProductThumbnailGallery gallery={previews} />
        </div>
        <div className="flex shrink-0 flex-col justify-between text-13px lg:w-[400px] xl:w-[520px] 3xl:w-[555px]">
          <div className="pb-7 xs:pb-8 lg:pb-10">
            <div className="pb-5 leading-[1.9em] rtl:text-right dark:text-light-600 xl:pb-6 3xl:pb-8">
              {description}
            </div>
            <div className="flex space-x-6 border-t border-light-500 py-3 rtl:space-x-reverse dark:border-dark-500 md:py-4 3xl:py-5">
              {!isFreeItem && (
                <div className="flex items-center tracking-[.1px] text-dark dark:text-light">
                  <ShoppingCartIcon className="h-[18px] w-[18px] text-dark-900 ltr:mr-2.5 rtl:ml-2.5 dark:text-light-800" />
                  {pluralize(t('text-sale'), orders_count, true)}
                </div>
              )}
              <div className="flex items-center tracking-[.1px] text-dark dark:text-light">
                <DownloadIcon className="h-[18px] w-[18px] text-dark-900 ltr:mr-2.5 rtl:ml-2.5 dark:text-light-800" />
                {pluralize(t('text-download'), total_downloads, true)}
              </div>
            </div>
            <ProductInformation
              tags={tags}
              created_at={created_at}
              updated_at={updated_at}
              layoutType={type.name}
              //@ts-ignore
              icon={type?.icon}
              className="border-t border-light-500 py-5 dark:border-dark-500 lg:py-6 3xl:py-10"
            />
            <div className="border-t border-light-500 pt-5 dark:border-dark-500">
              <ProductSocialShare productSlug={slug} />
            </div>
          </div>
          <div className="flex flex-col-reverse items-center xs:flex-row xs:gap-2.5 xs:pb-4 md:flex-nowrap md:gap-3.5 lg:gap-4 3xl:pb-14">
            {!isFreeItem ? (
              <AddToCart
                item={product}
                toastClassName="-mt-10 xs:mt-0"
                className="mt-2.5 w-full flex-1 xs:mt-0"
              />
            ) : (
              <FreeDownloadButton
                productId={id}
                productSlug={slug}
                productName={name}
                className="mt-2.5 w-full flex-1 xs:mt-0"
              />
            )}
            {Boolean(preview_url) && (
              <a
                href={preview_url}
                rel="noreferrer"
                target="_blank"
                className="transition-fill-colors flex min-h-[46px] w-full flex-1 items-center justify-center gap-2 rounded border border-light-500 bg-transparent py-3 px-4 font-semibold text-dark duration-200 hover:bg-light-400 hover:text-brand focus:bg-light-500 dark:border-dark-600 dark:text-light dark:hover:bg-dark-600 dark:focus:bg-dark-600 sm:h-12 md:px-5"
              >
                {t('text-live-preview')}
              </a>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
