import SelectInput from '@/components/ui/select-input';
import Label from '@/components/ui/label';
import { Control, useFormState, useWatch } from 'react-hook-form';
import { useEffect } from 'react';
import { useTagsQuery } from '@/data/tag';
import { useRouter } from 'next/router';
import { useTranslation } from 'next-i18next';

interface Props {
  control: Control<any>;
  setValue: any;
}

const ProductTagInput = ({ control, setValue }: Props) => {
  const { t } = useTranslation();
  const { locale } = useRouter();

  const { tags, loading } = useTagsQuery({
    limit: 999,
    language: locale,
  });

  return (
    <div>
      <Label>{t('sidebar-nav-item-tags')}</Label>
      <SelectInput
        name="tags"
        isMulti
        control={control}
        getOptionLabel={(option: any) => option.name}
        getOptionValue={(option: any) => option.id}
        // @ts-ignore
        options={tags}
        isLoading={loading}
      />
    </div>
  );
};

export default ProductTagInput;
