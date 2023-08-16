import type {
  SubmitHandler,
  UseFormReturn,
  UseFormProps,
  Path,
  FieldValues,
  UnpackNestedValue,
  DeepPartial,
} from 'react-hook-form';
import type { SchemaOf } from 'yup';
import { useForm } from 'react-hook-form';
import { yupResolver } from '@hookform/resolvers/yup';
import { useEffect } from 'react';

type ServerErrors<T> = {
  [Property in keyof T]: string;
};
type FormProps<TFormValues extends FieldValues> = {
  onSubmit: SubmitHandler<TFormValues>;
  children: (methods: UseFormReturn<TFormValues>) => React.ReactNode;
  useFormProps?: UseFormProps<TFormValues>;
  validationSchema?: SchemaOf<TFormValues>;
  serverError?: ServerErrors<Partial<TFormValues>> | null;
  resetFields?: any | null;
  [key: string]: unknown;
};

export const Form = <TFormValues extends FieldValues>({
  onSubmit,
  children,
  useFormProps,
  validationSchema,
  serverError,
  resetFields,
  ...formProps
}: FormProps<TFormValues>) => {
  const methods = useForm<TFormValues>({
    ...useFormProps,
    ...(validationSchema && { resolver: yupResolver(validationSchema) }),
  });
  useEffect(() => {
    if (serverError) {
      Object.entries(serverError).forEach(([key, value]) => {
        methods.setError(key as Path<TFormValues>, {
          type: 'manual',
          message: value,
        });
      });
    }
  }, [serverError, methods]);
  useEffect(() => {
    if (resetFields) {
      methods.reset(resetFields);
    }
  }, [resetFields, methods]);
  return (
    <form noValidate onSubmit={methods.handleSubmit(onSubmit)} {...formProps}>
      {children(methods)}
    </form>
  );
};
