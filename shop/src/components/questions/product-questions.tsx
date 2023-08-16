import QuestionCard from '@/components/questions/question-card';
import Pagination from '@/components/ui/pagination';
import { useEffect, useState } from 'react';
import { useModalAction } from '@/components/modal-views/context';
import { useRouter } from 'next/router';
import { useMe } from '@/data/user';
import isEmpty from 'lodash/isEmpty';
import QuestionSearch from './question-search';
import { useQuestions } from '@/data/question';
import { useTranslation } from 'next-i18next';

type ProductQuestionsProps = {
  className?: any;
  productId: string;
  shopId: string;
};

const ProductQuestions: React.FC<ProductQuestionsProps> = ({
  productId,
  shopId,
}) => {
  const [page, setPage] = useState(1);
  const { openModal } = useModalAction();
  const { query } = useRouter();
  const { isAuthorized } = useMe();
  const { t } = useTranslation('common');
  const { questions, paginatorInfo } = useQuestions({
    product_id: productId,
    limit: 5,
    page,
    ...(!isEmpty(query?.text) && { question: query.text?.toString() }),
  });

  useEffect(() => {
    setPage(1);
  }, [query.text]);

  function onPagination(current: number) {
    setPage(current);
  }

  const openQuestionModal = () => {
    if (!isAuthorized) {
      openModal('LOGIN_VIEW');
      return;
    }
    openModal('QUESTION_FORM', { product_id: productId, shop_id: shopId });
  };

  return (
    <div className="block">
      <div className="flex w-[calc(100%+32px)] flex-col justify-between border-b border-t border-light-500 px-4 py-5 ltr:-ml-4 rtl:-mr-4 dark:border-dark-400 sm:flex-row sm:items-center md:mb-1 md:w-full md:border-t-0 md:border-b-0 md:py-0 md:px-0 ltr:md:ml-0 rtl:md:mr-0">
        <h2 className="text-sm tracking-tight text-dark dark:text-light">
          {t('text-question-and-answers')} ({paginatorInfo?.total ?? 0})
        </h2>
        <div className="mt-4 inline-flex flex-col-reverse items-start gap-2.5 sm:mt-0 sm:flex-col sm:items-end ltr:sm:pl-2 rtl:sm:pr-2 md:flex-row">
          <div className="min-w-full sm:min-w-[280px]">
            <QuestionSearch label="Search" />
          </div>
          <button
            className="grow-0 rounded-md bg-brand px-5 py-3 text-13px font-semibold leading-5 text-light transition-colors hover:bg-brand-dark"
            onClick={openQuestionModal}
          >
            {t('text-ask-question')}
          </button>
        </div>
      </div>
      {questions?.length !== 0 ? (
        <div className="">
          <div className="">
            {questions?.map((question) => (
              <QuestionCard
                key={`question-no-${question?.id}`}
                question={question}
              />
            ))}
            {/* Pagination */}
            {paginatorInfo && (
              <div className="flex flex-col items-center justify-between space-y-1 border-t border-light-500 py-5 dark:border-dark-400 md:flex-row md:space-y-0 md:py-3 md:pb-5 lg:pb-3">
                <div className="text-13px text-dark-700 dark:text-light-900 md:mt-2">
                  {t('text-page')} {paginatorInfo.currentPage} {t('text-of')}{' '}
                  {Math.ceil(paginatorInfo.total / paginatorInfo.perPage)}
                </div>

                <div className="mb-2 flex items-center">
                  <Pagination
                    total={paginatorInfo.total}
                    current={paginatorInfo.currentPage}
                    pageSize={paginatorInfo.perPage}
                    onChange={onPagination}
                  />
                </div>
              </div>
            )}
          </div>
        </div>
      ) : (
        <div className="flex flex-col items-center justify-center border-b border-light-500 px-5 py-16 dark:border-dark-400">
          <h3 className="text-lg font-semibold text-dark-600 dark:text-light-600">
            {t('text-no-question-found')}
          </h3>
        </div>
      )}
    </div>
  );
};

export default ProductQuestions;
