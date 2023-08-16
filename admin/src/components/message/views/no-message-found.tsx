import React from 'react';
import { useTranslation } from 'next-i18next';
import { NoMessageFound } from '@/components/icons/no-message-found';

const MessageNotFound = ({ ...rest }) => {
  const { t } = useTranslation();
  return (
    <>
      <div className="flex h-full" {...rest}>
        <div className="m-auto">
          <div className="mb-8">
            <NoMessageFound />
          </div>
          <p className="font-semibold text-[#686D73]">
            {t('text-no-message-found')}
          </p>
        </div>
      </div>
    </>
  );
};

export default MessageNotFound;
