import Search from '@/components/search/search';
import { useRouter } from 'next/router';
import { useSearch } from '@/components/search/search.context';
import { useTranslation } from 'next-i18next';

interface Props {
  [key: string]: unknown;
}

const QuestionSearch: React.FC<Props> = ({ ...props }) => {
  const router = useRouter();
  const { searchTerm, updateSearchTerm } = useSearch();
  const { t } = useTranslation('common');

  const handleOnChange = (e: any) => {
    const { value } = e.target;
    updateSearchTerm(value);
  };

  const onSearch = (e: any) => {
    e.preventDefault();
    if (!searchTerm) return;
    const { pathname, query } = router;
    router.push(
      {
        pathname,
        query: { ...query, text: searchTerm },
      },
      undefined,
      {
        scroll: false,
      }
    );
  };

  function clearSearch() {
    updateSearchTerm('');
    const { pathname, query } = router;
    const { text, ...rest } = query;
    if (text) {
      router.push(
        {
          pathname,
          query: { ...rest },
        },
        undefined,
        {
          scroll: false,
        }
      );
    }
  }

  return (
    <Search
      label="search"
      onSubmit={onSearch}
      onClearSearch={clearSearch}
      onChangeSearch={handleOnChange}
      value={searchTerm}
      name="search"
      placeholder={t('text-search-question')}
      {...props}
    />
  );
};

export default QuestionSearch;
