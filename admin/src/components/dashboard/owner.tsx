import { useTranslation } from 'next-i18next';
import { Fragment } from 'react';
import { Tab } from '@headlessui/react';
import cn from 'classnames';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { isEmpty } from 'lodash';
const ShopList = dynamic(() => import('@/components/dashboard/shops/shops'));
import { adminOnly, getAuthCredentials, hasAccess } from '@/utils/auth-utils';

const tabList = [
  {
    title: 'common:sidebar-nav-item-my-shops',
    children: 'ShopList',
  },
];

const MAP_PAGE_LIST: Record<string, any> = {
  ShopList: ShopList,
};

const OwnerShopLayout = () => {
  const { t } = useTranslation();
  const router = useRouter();
  const { query } = router;

  const classNames = {
    basic:
      'lg:text-[1.375rem] font-semibold border-b-2 border-solid border-transparent lg:pb-5 pb-3 -mb-0.5',
    selected: 'text-accent hover:text-accent-hover border-current',
    normal: 'hover:text-black/80',
  };

  return (
    <>
      <Tab.Group
        defaultIndex={
          !isEmpty(query?.tab) && query?.tab ? Number(query?.tab) : 0
        }
        onChange={(index: any) => {
          router.push({
            query: { tab: index },
          });
        }}
      >
        <Tab.List className="flex flex-wrap gap-x-9 border-b-2 border-solid border-b-[#E4E1E7]">
          {tabList?.map((tab, key) => {
            let { title } = tab;
            return (
              <Tab as={Fragment} key={key}>
                {({ selected }) => (
                  <button
                    className={cn(
                      selected ? classNames?.selected : classNames?.normal,
                      classNames?.basic
                    )}
                  >
                    {t(title)}
                  </button>
                )}
              </Tab>
            );
          })}
        </Tab.List>
        <Tab.Panels
          className="mt-4 lg:mt-8"
          style={{ height: 'calc(100% - 94px)' }}
        >
          {tabList?.map((tab, key) => {
            let { children } = tab;
            const Component = MAP_PAGE_LIST[children];
            return (
              <Tab.Panel key={key} className="h-full">
                <Component />
              </Tab.Panel>
            );
          })}
        </Tab.Panels>
      </Tab.Group>
    </>
  );
};

const OwnerDashboard = () => {
  const { permissions } = getAuthCredentials();
  let permission = hasAccess(adminOnly, permissions);

  return permission ? <ShopList /> : <OwnerShopLayout />;
};

export default OwnerDashboard;
