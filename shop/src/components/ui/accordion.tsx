import { Disclosure, Transition } from '@headlessui/react';
import { ChevronRight } from '@/components/icons/chevron-right';
import { useTranslation } from 'next-i18next';

type CollapseProps = {
  item: any;
};

export const Accordion: React.FC<CollapseProps> = ({ item }) => {
  const { title, content } = item;
  const { t } = useTranslation('common');
  return (
    <div className="shadow-category group mx-auto mb-4 w-full rounded">
      <Disclosure>
        {({ open }) => (
          <>
            <Disclosure.Button
              className={`relative flex w-full justify-between rounded border border-light-400 px-5 py-4 text-left text-dark focus:outline-none dark:border-dark-400 dark:text-light sm:py-5 ${
                open
                  ? 'bg-light-200 dark:bg-dark-300'
                  : 'bg-light dark:bg-dark-200'
              }`}
            >
              <span className="text-sm font-medium">{t(title)}</span>
              <ChevronRight
                className={`-mr-2 mt-0.5 h-4 w-4 flex-shrink-0 text-dark/60 transition-all group-hover:text-dark rtl:rotate-180 dark:text-light/70 dark:group-hover:text-light sm:h-[18px] sm:w-[18px] lg:-mr-1.5 ${
                  open ? 'ltr:rotate-90 rtl:rotate-90' : ''
                }`}
              />
            </Disclosure.Button>

            <Transition
              show={open}
              enter="transition duration-500 ease-out"
              enterFrom="transform scale-5 opacity-0"
              enterTo="transform scale-10 opacity-100"
              leave="transition duration-75 ease-out"
              leaveFrom="transform scale-100 opacity-100"
              leaveTo="transform scale-5 opacity-0"
            >
              {open && (
                <Disclosure.Panel static>
                  <div className="px-5 py-3 leading-7 3xl:px-6 3xl:pt-5">
                    {t(content)}
                  </div>
                </Disclosure.Panel>
              )}
            </Transition>
          </>
        )}
      </Disclosure>
    </div>
  );
};

export default Accordion;
