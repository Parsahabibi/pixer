import Image from 'next/image';
import { useModalState } from '@/components/modal-views/context';
import { ChevronLeft } from '@/components/icons/chevron-left';
import { ChevronRight } from '@/components/icons/chevron-right';
import placeholder from '@/assets/images/placeholders/product.svg';
import {
  Swiper,
  SwiperSlide,
  SwiperOptions,
  Navigation,
  Thumbs,
} from '@/components/ui/slider';
import { useRef, useState } from 'react';

const swiperParams: SwiperOptions = {
  slidesPerView: 1,
  spaceBetween: 0,
};

export default function ReviewImageModal() {
  const { data } = useModalState();
  let [thumbsSwiper, setThumbsSwiper] = useState<any>(null);
  const prevRef = useRef<HTMLDivElement>(null);
  const nextRef = useRef<HTMLDivElement>(null);

  return (
    <div className="m-auto block w-full rounded p-6 xs:w-[450px] xs:p-5 md:w-[590px] lg:w-[720px] lg:p-7">
      <div className="relative mb-3 w-full overflow-hidden xs:mt-8 md:mt-10 xl:mb-5">
        <Swiper
          id="reviewGallery"
          speed={400}
          allowTouchMove={false}
          thumbs={{ swiper: thumbsSwiper }}
          modules={[Navigation, Thumbs]}
          navigation={{
            prevEl: prevRef.current!,
            nextEl: nextRef.current!,
          }}
          {...swiperParams}
        >
          {data?.images?.map((item: any) => (
            <SwiperSlide
              key={`review-gallery-${item.id}`}
              className="relative flex aspect-[3/2] items-center justify-center bg-light-200 dark:bg-dark-200"
            >
              <Image
                fill
                src={item?.original ?? placeholder}
                alt={`Product gallery ${item.id}`}
                className="object-contain"
              />
            </SwiperSlide>
          ))}
        </Swiper>
        <div className="absolute top-2/4 z-10 flex w-full items-center justify-between px-2.5 xl:px-4">
          <div
            ref={prevRef}
            className="flex h-8 w-8 -translate-y-1/2 transform cursor-pointer items-center justify-center rounded-full border border-light-400 bg-light text-dark/90 shadow-xl transition duration-300 hover:bg-light-200 hover:text-brand-dark focus:outline-none xl:h-9 xl:w-9"
          >
            <ChevronLeft className="h-4 w-4 xl:h-[18px] xl:w-[18px]" />
          </div>
          <div
            ref={nextRef}
            className="flex h-8 w-8 -translate-y-1/2 transform cursor-pointer items-center justify-center rounded-full border border-light-400 bg-light text-dark/90 shadow-xl transition duration-300 hover:bg-light-200 hover:text-brand-dark focus:outline-none xl:h-9 xl:w-9"
          >
            <ChevronRight className="h-4 w-4 xl:h-[18px] xl:w-[18px]" />
          </div>
        </div>
      </div>
      <div className="flex-shrink-0">
        <Swiper
          id="reviewGalleryThumbs"
          freeMode={true}
          observer={true}
          slidesPerView={4}
          onSwiper={setThumbsSwiper}
          observeParents={true}
          watchSlidesProgress={true}
        >
          {data?.images?.map((item: any) => (
            <SwiperSlide
              key={`review-thumb-gallery-${item.id}`}
              className="relative flex aspect-[3/2] cursor-pointer items-center justify-center border border-light-500 transition hover:opacity-75 dark:border-dark-500"
            >
              <Image
                fill
                src={item?.thumbnail ?? placeholder}
                alt={`Review thumb gallery ${item.id}`}
                className="object-cover"
              />
            </SwiperSlide>
          ))}
        </Swiper>
      </div>
    </div>
  );
}
