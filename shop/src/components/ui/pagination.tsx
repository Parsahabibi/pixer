import RCPagination, { PaginationProps } from 'rc-pagination';
import React, { ReactNode } from 'react';
import 'rc-pagination/assets/index.css';
import { useTranslation } from 'next-i18next';

const Pagination: React.FC<PaginationProps> = (props) => {
  const { t } = useTranslation('common');
  const textItemRender = (
    current: ReactNode,
    type: string,
    element: ReactNode
  ) => {
    if (type === 'prev') {
      return t('text-prev');
    }
    if (type === 'next') {
      return t('text-next');
    }
    return element;
  };

  return <RCPagination itemRender={textItemRender} {...props} />;
};

export default Pagination;
