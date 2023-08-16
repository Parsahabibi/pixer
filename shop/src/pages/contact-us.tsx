import { serverSideTranslations } from 'next-i18next/serverSideTranslations';
import type { GetStaticProps } from 'next';
import { useTranslation } from 'next-i18next';
import type { CreateContactUsInput, NextPageWithLayout } from '@/types';
import React, { useState } from 'react';
import { useMutation } from 'react-query';
import type { SubmitHandler } from 'react-hook-form';
import toast from 'react-hot-toast';
import GeneralLayout from '@/layouts/_general-layout';
import { LocationIcon } from '@/components/icons/contact/location-icon';
import { PhoneIcon } from '@/components/icons/contact/phone-icon';
import { MailIcon } from '@/components/icons/contact/mail-icon';
import { Form } from '@/components/ui/forms/form';
import Input from '@/components/ui/forms/input';
import Textarea from '@/components/ui/forms/textarea';
import Seo from '@/layouts/_seo';
import Button from '@/components/ui/button';
import client from '@/data/client';
import PageHeading from '@/components/ui/page-heading';
import routes from '@/config/routes';
import * as yup from 'yup';
import { useSettings } from '@/data/settings';

function ContactInfo({
  icon,
  title,
  description,
}: {
  icon: React.ReactNode;
  title: string;
  description: string;
}) {
  return (
    <div className="flex max-w-xs flex-row items-center pr-4 sm:pr-2 lg:max-w-sm lg:pr-0">
      <div className="flex w-12 flex-shrink-0 justify-center text-brand">
        {icon}
      </div>
      <div className="mt-0 ltr:pl-5 rtl:pr-5">
        <h3 className="mb-2 text-15px font-medium text-dark dark:text-light">
          {title}
        </h3>
        <p className="leading-[1.8em]">{description}</p>
      </div>
    </div>
  );
}

const contactUsFormSchema = yup.object().shape({
  name: yup.string().required(),
  email: yup.string().email().required(),
  subject: yup.string().required(),
  description: yup.string().required(),
});

const ContactUsPage: NextPageWithLayout = () => {
  const { t } = useTranslation('common');
  const { settings } = useSettings();
  const { contactDetails } = settings ?? {};
  let [reset, setReset] = useState<CreateContactUsInput | null>(null);
  const { mutate } = useMutation(client.settings.contactUs, {
    onSuccess: () => {
      toast.success('Successfully sent your message');
      setReset({
        name: '',
        email: '',
        subject: '',
        description: '',
      });
    },
    onError: (res) => {
      toast.error('Ops! something went wrong');
      console.log(res);
    },
  });
  const onSubmit: SubmitHandler<CreateContactUsInput> = (values) => {
    mutate(values);
  };
  return (
    <>
      <Seo
        title="Contact us"
        description="Fastest digital download template built with React, NextJS, TypeScript, React-Query and Tailwind CSS."
        url={routes.contact}
      />
      <div className="mx-auto flex h-full w-full max-w-screen-xl flex-col p-4 sm:p-5">
        <PageHeading
          title={t('contact-us-title')}
          subtitle={t('contact-us-subtitle')}
        />
        <div className="flex w-full flex-col overflow-hidden rounded-md px-4 py-5 sm:px-6 sm:py-8 md:bg-light md:p-10 md:shadow-card md:dark:bg-dark-200 md:dark:shadow-none lg:flex-row lg:p-0">
          <div className="shrink-0 border-light-300 dark:border-dark-300 lg:w-[400px] lg:py-10 ltr:lg:border-r ltr:lg:pr-[72px] ltr:lg:pl-10 rtl:lg:border-l rtl:lg:pl-[72px] rtl:lg:pr-10 lg:dark:bg-dark-250 xl:w-[430px] xl:py-12 ltr:xl:pr-24 rtl:xl:pl-24">
            <h2 className="pb-2 text-lg font-medium text-dark dark:text-light md:text-xl">
              {t('contact-us-info-title')}
            </h2>
            <p className="font-medium leading-[1.8em]">
              {t('contact-us-info-subtitle')}
            </p>
            <div className="grid-cols-2 gap-x-5 gap-y-8 space-y-7 pt-9 sm:grid sm:space-y-0 md:gap-y-9 lg:block lg:space-y-9">
              <ContactInfo
                icon={<LocationIcon className="h-12 w-12" />}
                title={t('contact-us-office-title')}
                description={
                  contactDetails?.location?.formattedAddress ??
                  t('contact-us-office-message')
                }
              />
              <ContactInfo
                icon={<PhoneIcon className="h-10 w-10" />}
                title={t('contact-us-phone-title')}
                description={
                  contactDetails?.contact ?? t('contact-us-phone-message')
                }
              />
              <ContactInfo
                icon={<MailIcon className="h-10 w-10" />}
                title={t('contact-us-site-title')}
                description={
                  contactDetails?.website ?? t('contact-us-site-message')
                }
              />
            </div>
          </div>
          <div className="w-full flex-grow pt-12 lg:p-10 xl:p-12">
            <Form<CreateContactUsInput>
              onSubmit={onSubmit}
              validationSchema={contactUsFormSchema}
              resetFields={reset}
            >
              {({ register, formState: { errors } }) => (
                <>
                  <fieldset className="mb-6 grid gap-5 sm:grid-cols-2">
                    <Input
                      label={t('contact-us-name-field')}
                      {...register('name')}
                      error={errors.name?.message}
                    />
                    <Input
                      label={t('contact-us-email-field')}
                      type="email"
                      {...register('email')}
                      error={errors.email?.message}
                    />
                    <Input
                      label={t('contact-us-subject-field')}
                      {...register('subject')}
                      error={errors.subject?.message}
                      className="sm:col-span-2"
                    />
                    <Textarea
                      label={t('contact-us-message-field')}
                      {...register('description')}
                      error={errors.description?.message}
                      className="sm:col-span-2"
                    />
                  </fieldset>
                  <Button
                    type="submit"
                    className="mb-1 w-full flex-1 sm:flex-none md:w-auto"
                  >
                    {t('contact-us-submit-button')}
                  </Button>
                </>
              )}
            </Form>
          </div>
        </div>
      </div>
    </>
  );
};

ContactUsPage.getLayout = function getLayout(page) {
  return <GeneralLayout>{page}</GeneralLayout>;
};

export const getStaticProps: GetStaticProps = async ({ locale }) => {
  return {
    props: {
      ...(await serverSideTranslations(locale!, ['common'])),
    },
    revalidate: 60, // In seconds
  };
};

export default ContactUsPage;
