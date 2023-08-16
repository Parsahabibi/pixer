import * as yup from 'yup';

export const productValidationSchema = yup.object().shape({
  name: yup.string().required('form:error-name-required'),
  sku: yup.string().nullable().required('form:error-sku-required'),
  price: yup
    .number()
    .typeError('form:error-price-must-number')
    .min(0)
    .required('form:error-price-required'),
  quantity: yup
    .number()
    .typeError('form:error-quantity-must-number')
    .positive('form:error-quantity-must-positive')
    .integer('form:error-quantity-must-integer')
    .required('form:error-quantity-required'),
  unit: yup.string().required('form:error-unit-required'),
  type: yup.object().nullable().required('form:error-type-required'),

  // digital_file_input: yup.mixed().when('is_digital', (isDigital) => {
  //   if (isDigital) {
  //     return yup
  //       .object()
  //       .test(
  //         'check-digital-file',
  //         'form:error-digital-file-input-required',
  //         (file) => file && file?.original
  //       );
  //   }
  //   return yup.string().nullable();
  // }),

  // digital_file_input: yup
  //   .object()
  //   .test(
  //     'check-digital-file',
  //     'form:error-digital-file-input-required',
  //     (file) => file && file?.original
  //   ),

  status: yup.string().required('form:error-status-required'),
});
