import { useState } from 'react';
import dynamic from 'next/dynamic';
import cn from 'classnames';
import { motion } from 'framer-motion';
import Header from '@/layouts/_header';
import { Sidebar } from '@/layouts/_layout-sidebar';
import { useBreakpoint } from '@/lib/hooks/use-breakpoint';
import { useIsMounted } from '@/lib/hooks/use-is-mounted';
import {useRouter} from "next/router";
const BottomNavigation = dynamic(() => import('@/layouts/_bottom-navigation'), {
  ssr: false,
});

export default function Layout({ children }: React.PropsWithChildren<{}>) {
  const breakpoint = useBreakpoint();
  const isMounted = useIsMounted();
  let [collapse, setCollapse] = useState(false);
  function toggleSidebar() {
    setCollapse((prev) => !prev);
  }

  const { locale } = useRouter();


  const dir = locale === 'ar' || locale === 'he' || locale === 'fa' ? 'rtl' : 'ltr';


  return (
    <motion.div
      initial="exit"
      animate="enter"
      exit="exit"
      className="flex min-h-screen w-full flex-col bg-light-300 dark:bg-dark-100"
    >
      <Header
        isCollapse={collapse}
        showHamburger={true}
        onClickHamburger={toggleSidebar}
      />
      <div dir={dir} className="flex flex-1">
        <Sidebar isCollapse={collapse} />
        <main
          className={cn(
            'flex w-full flex-col',
            collapse
              ? 'ltr:sm:pl-60 rtl:sm:pr-60 ltr:xl:pl-[75px] rtl:xl:pr-[75px]'
              : 'ltr:sm:pl-[75px] rtl:sm:pr-[75px] ltr:xl:pl-60 rtl:xl:pr-60'
          )}
        >
          {children}
        </main>
      </div>
      {isMounted && breakpoint === 'xs' && <BottomNavigation />}
    </motion.div>
  );
}
