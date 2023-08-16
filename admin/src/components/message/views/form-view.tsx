import { useTranslation } from 'next-i18next';
import { useRouter } from 'next/router';
import Button from '@/components/ui/button';
import { SendMessageIcon } from '@/components/icons/send-message';
import { toast } from 'react-toastify';
import { useSendMessage } from '@/data/conversations';
import * as yup from 'yup';
import TextArea from '@/components/ui/text-area';
import { useEffect } from 'react';
import { useForm } from 'react-hook-form';
import { yupResolver } from '@hookform/resolvers/yup';
import { isEmpty } from 'lodash';
import { useState } from 'react';

type FormValues = {
  message: string;
};

const messageSchema = yup.object().shape({
  message: yup.string().required('error-body-required'),
});

interface Props {
  className?: string;
}

const CreateMessageForm = ({ className, ...rest }: Props) => {
  const {
    register,
    handleSubmit,
    getValues,
    setFocus,
    reset,
    formState: { errors },
  } = useForm<FormValues>({
    resolver: yupResolver(messageSchema),
  });

  const { t } = useTranslation();
  const router = useRouter();
  const { query } = router;
  const { mutate: createMessage, isLoading: creating } = useSendMessage();
  useEffect(() => {
    const listener = (event: any) => {
      if (event.key === 'Enter' && event.shiftKey) {
        return false;
      }
      if (event.code === 'Enter' || event.code === 'NumpadEnter') {
        event.preventDefault();
        const values = getValues();
        onSubmit(values);
      }
    };
    document.addEventListener('keydown', listener);
    return () => {
      document.removeEventListener('keydown', listener);
    };
  }, [query?.id]);
  const onSubmit = async (values: FormValues) => {
    if (isEmpty(values.message)) {
      toast?.error('Message is required');
      return;
    }
    createMessage(
      {
        message: values?.message,
        id: query?.id as string,
      },
      {
        onError: (error: any) => {
          toast?.error(error?.message);
        },
        onSuccess: () => {
          const chatBody = document.getElementById('chatBody');
          chatBody?.scrollTo({
            top: chatBody?.scrollHeight,
            behavior: 'smooth',
          });
          reset();
        },
      }
    );
  };
  useEffect(() => {
    setFocus('message');
  }, [setFocus]);
  return (
    <>
      <form noValidate onSubmit={handleSubmit(onSubmit)}>
        <div className="relative">
          {!!creating ? (
            <div className="absolute top-0 left-0 z-50 flex h-full w-full cursor-not-allowed bg-[#EEF1F4]/50">
              <div className="m-auto h-5 w-4 animate-spin rounded-full border-2 border-t-2 border-transparent border-t-accent"></div>
            </div>
          ) : (
            ''
          )}
          <TextArea
            className="overflow-y-auto overflow-x-hidden shadow-chatBox"
            placeholder="Type your message here.."
            {...register('message')}
            variant="solid"
            inputClassName="!border-0 bg-white pr-12 block !h-full"
            rows={3}
            disabled={!!creating}
          />
          <div className="absolute top-0 right-0 h-full">
            <Button
              className="!h-full px-4 text-lg focus:!shadow-none focus:!ring-0"
              variant="custom"
              disabled={!!creating}
            >
              <SendMessageIcon />
            </Button>
          </div>
        </div>
      </form>
    </>
  );
};

export default CreateMessageForm;
