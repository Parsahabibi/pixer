import * as yup from 'yup';
import Button from '@/components/ui/button';
import { Form } from '@/components/ui/forms/form';
import TextArea from '@/components/ui/forms/textarea';
import { CreateQuestionInput } from '@/types';
import {
  useModalAction,
  useModalState,
} from '@/components/modal-views/context';
import { useMutation, useQueryClient } from 'react-query';
import client from '@/data/client';
import toast from 'react-hot-toast';
import { API_ENDPOINTS } from '@/data/client/endpoints';
import { useTranslation } from 'next-i18next';

const questionFormSchema = yup.object().shape({
  question: yup.string().required('Tell us more about it'),
});

export default function QuestionForm() {
  const { data } = useModalState();
  const { t } = useTranslation('common');
  const { closeModal } = useModalAction();
  const queryClient = useQueryClient();
  const { mutate: createQuestion, isLoading } = useMutation(
    client.questions.create,
    {
      onSuccess: () => {
        toast.success(t('text-ask-question-success'));
      },
      onError: (error) => {
        // TODO: replace it after implement translation
        const {
          response: { data },
        }: any = error ?? {};

        if (data?.message?.includes('MAXIMUM_QUESTION_LIMIT_EXCEEDED')) {
          toast.error(t('text-ask-question-error'));
        } else {
          toast.error(data?.message);
        }
      },
      onSettled: () => {
        queryClient.refetchQueries([API_ENDPOINTS.PRODUCTS_QUESTIONS]);
        closeModal();
      },
    }
  );
  const onSubmit = (values: Pick<CreateQuestionInput, 'question'>) => {
    createQuestion({
      product_id: data.product_id,
      shop_id: data.shop_id,
      question: values.question,
    });
  };
  return (
    <div className="flex h-full min-h-screen w-screen flex-col justify-center bg-light text-left dark:bg-dark-250 xs:h-auto xs:min-h-0 xs:max-w-[400px] md:max-w-[590px] md:rounded-xl lg:max-w-[650px]">
      <h3 className="mb-2 pl-6 pr-6 text-center text-base font-medium tracking-[-0.3px] text-dark dark:text-light xs:mb-0 xs:border-b xs:border-dark/10 xs:py-4 xs:pr-10 xs:text-left xs:dark:border-light/10 md:py-5 md:pl-7 lg:py-6 lg:pr-16">
        {t('text-ask-seller-question')}
      </h3>
      <div className="p-6 md:p-7">
        <Form<Pick<CreateQuestionInput, 'question'>>
          onSubmit={onSubmit}
          validationSchema={questionFormSchema}
        >
          {({ register, formState: { errors } }) => (
            <>
              <TextArea
                {...register('question')}
                className="mb-5"
                error={errors?.question?.message}
              />

              <div className="flex items-center justify-between">
                <span className="text-xs leading-5 text-dark/60 ltr:pr-8 rtl:pl-8 dark:text-light/60">
                  {t('text-ask-question-desc')}
                </span>
                <Button
                  className="text-sm"
                  isLoading={isLoading}
                  disabled={isLoading}
                >
                  {t('text-submit')}
                </Button>
              </div>
            </>
          )}
        </Form>
      </div>
    </div>
  );
}
