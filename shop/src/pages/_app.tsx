import type { AppProps } from 'next/app';
import type { NextPageWithLayout } from '@/types';
import { useEffect, useState } from 'react';
import { Hydrate, QueryClient, QueryClientProvider } from 'react-query';
import { ReactQueryDevtools } from 'react-query/devtools';
import { AnimatePresence } from 'framer-motion';
import { Toaster } from 'react-hot-toast';
import { ThemeProvider } from 'next-themes';
import { appWithTranslation } from 'next-i18next';
import { validateEnvironmentVariables } from '@/config/validate-environment-variables';
import { CartProvider } from '@/components/cart/lib/cart.context';
import { ModalProvider } from '@/components/modal-views/context';
import ModalsContainer from '@/components/modal-views/container';
import DrawersContainer from '@/components/drawer-views/container';
import SearchView from '@/components/search/search-view';
import DefaultSeo from '@/layouts/_default-seo';
import { SearchProvider } from '@/components/search/search.context';

// base css file
import '@/assets/css/scrollbar.css';
import '@/assets/css/swiper-carousel.css';
import '@/assets/css/pagination.css';
import '@/assets/css/globals.css';

import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { getDirection } from '@/lib/constants';

const PrivateRoute = dynamic(() => import('@/layouts/_private-route'), {
  ssr: false,
});

validateEnvironmentVariables();

type AppPropsWithLayout = AppProps & {
  Component: NextPageWithLayout;
};

function CustomApp({ Component, pageProps }: AppPropsWithLayout) {
  const { locale } = useRouter();
  const [queryClient] = useState(() => new QueryClient());

  const getLayout = Component.getLayout ?? ((page) => page);
  const dir = getDirection(locale);
  useEffect(() => {
    document.documentElement.dir = dir;
  }, [dir]);
  const authenticationRequired = Component.authorization ?? false;
  return (
    <QueryClientProvider client={queryClient}>
      <Hydrate state={pageProps.dehydratedState}>
        <ThemeProvider
          attribute="class"
          defaultTheme="dark"
          enableSystem={false}
        >
          <SearchProvider>
            <CartProvider>
              <ModalProvider>
                <AnimatePresence
                  initial={false}
                  onExitComplete={() => window.scrollTo(0, 0)}
                >
                  <>
                    <DefaultSeo />
                    {authenticationRequired ? (
                      <PrivateRoute>
                        {getLayout(<Component {...pageProps} />)}
                      </PrivateRoute>
                    ) : (
                      getLayout(<Component {...pageProps} />)
                    )}
                    <SearchView />
                    <ModalsContainer />
                    <DrawersContainer />
                    <Toaster containerClassName="!top-16 sm:!top-3.5 !bottom-16 sm:!bottom-3.5" />
                  </>
                </AnimatePresence>
              </ModalProvider>
            </CartProvider>
          </SearchProvider>
        </ThemeProvider>
      </Hydrate>
      <ReactQueryDevtools initialIsOpen={false} position="bottom-right" />
    </QueryClientProvider>
  );
}

export default appWithTranslation(CustomApp);
