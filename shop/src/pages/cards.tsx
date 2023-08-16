import Seo from '@/layouts/_seo';
import MyCards from '@/components/card/my-cards';
import { useSettings } from '@/data/settings';
import { NextPageWithLayout, PaymentGateway } from '@/types';
import { fadeInBottom } from '@/lib/framer-motion/fade-in-bottom';
import { motion } from 'framer-motion';
import type { GetStaticProps } from 'next';
import DashboardLayout from '@/layouts/_dashboard';
import { serverSideTranslations } from 'next-i18next/serverSideTranslations';
import { useTranslation } from 'next-i18next';

const MyCardsPage: NextPageWithLayout = () => {
  const { settings } = useSettings();

  // Make it dynamic
  if (
    ![PaymentGateway.STRIPE]?.includes(
      settings?.paymentGateway?.toUpperCase() as PaymentGateway
    )
  ) {
    return null;
  }

  return (
    <>
      <Seo
        title="My cards"
        description="All my card related information."
        url="/cards"
      />
      <motion.div
        variants={fadeInBottom()}
        className="flex min-h-full flex-grow flex-col"
      >
        <MyCards />
      </motion.div>
    </>
  );
};

MyCardsPage.authorization = true;
MyCardsPage.getLayout = function getLayout(page) {
  return <DashboardLayout>{page}</DashboardLayout>;
};

export const getStaticProps: GetStaticProps = async ({ locale }) => {
  return {
    props: {
      ...(await serverSideTranslations(locale!, ['common'])),
    },
    revalidate: 60, // In seconds
  };
};

export default MyCardsPage;
