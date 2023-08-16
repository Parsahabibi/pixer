import { serverSideTranslations } from 'next-i18next/serverSideTranslations';
import type { GetStaticProps } from 'next';
import { useTranslation } from 'next-i18next';
import type { NextPageWithLayout } from '@/types';
import DashboardLayout from '@/layouts/_dashboard';
import { useMyReports } from '@/data/report';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';
import { Table } from '@/components/ui/table';
import TableLoader from '@/components/ui/loader/table-loader';
import ItemNotFound from '@/components/ui/item-not-found';
import ErrorMessage from '@/components/ui/error-message';

const MyReportsPage: NextPageWithLayout = () => {
  const { t } = useTranslation('common');
  const { reports, isLoading, error } = useMyReports({
    limit: 50,
  });

  if (error) return <ErrorMessage message={error?.message} />;

  const orderTableColumns = [
    {
      title: 'ID',
      dataIndex: 'id',
      key: 'id',
      align: 'center',
      className: '!text-sm',
      width: 80,
    },
    {
      title: 'Message',
      dataIndex: 'message',
      key: 'message',
      align: 'center',
      className: '!text-sm min-w-fit',
      // width: 300,
    },
    {
      title: 'Date',
      dataIndex: 'created_at',
      key: 'created_at',
      align: 'center',
      className: '!text-sm  ',
      width: 160,
      render: (date: string) => {
        dayjs.extend(relativeTime);
        dayjs.extend(utc);
        dayjs.extend(timezone);
        return (
          <span className=" whitespace-nowrap">
            {dayjs.utc(date).tz(dayjs.tz.guess()).fromNow()}
          </span>
        );
      },
    },
  ];

  // loader
  if (!reports.length && isLoading) {
    return (
      <div className="flex w-full flex-col">
        <div className="mb-8 flex items-center justify-center sm:mb-10">
          <h1 className="text-heading text-center text-lg font-semibold sm:text-xl">
            {t('text-my-reports-title')}
          </h1>
        </div>

        <TableLoader uniqueKey={`table-loader`} />
      </div>
    );
  }

  if (!reports.length && !isLoading) {
    return (
      <div className="flex w-full flex-col">
        <div className="mb-8 flex items-center justify-between sm:mb-10">
          <h1 className="mb-3 text-15px font-medium text-dark dark:text-light">
            {t('text-my-reports-title')}
          </h1>
        </div>
        <ItemNotFound title={t('text-no-reports-found')} message="" />
      </div>
    );
  }

  return (
    <div className="flex w-full flex-col">
      <h1 className="mb-8 text-15px font-medium text-dark dark:text-light">
        {t('text-my-reports-title')}
      </h1>
      <Table
        //@ts-ignore
        columns={orderTableColumns}
        data={reports}
        rowKey={(record: any) => record.created_at}
        className="shadow-none"
        rowClassName="!cursor-auto"
      />
    </div>
  );
};

MyReportsPage.authorization = true;
MyReportsPage.getLayout = function getLayout(page) {
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

export default MyReportsPage;
