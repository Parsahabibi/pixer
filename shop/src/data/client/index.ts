import type {
  AuthResponse,
  CategoryPaginator,
  CategoryQueryOptions,
  ForgetPasswordInput,
  LoginUserInput,
  Order,
  OrderedFilePaginator,
  OrderPaginator,
  OrderQueryOptions,
  PasswordChangeResponse,
  Product,
  ProductPaginator,
  ProductQueryOptions,
  RegisterUserInput,
  ResetPasswordInput,
  Settings,
  Shop,
  ShopPaginator,
  ShopQueryOptions,
  Tag,
  TagPaginator,
  UpdateProfileInput,
  User,
  QueryOptions,
  CreateContactUsInput,
  VerifyForgetPasswordTokenInput,
  ChangePasswordInput,
  PopularProductsQueryOptions,
  CreateOrderInput,
  CheckoutVerificationInput,
  VerifiedCheckoutResponse,
  TopShopQueryOptions,
  Attachment,
  WishlistQueryOption,
  WishlistPaginator,
  Wishlist,
  ReviewQueryOptions,
  Review,
  CreateReviewInput,
  ReviewResponse,
  UpdateReviewInput,
  ReviewPaginator,
  QuestionQueryOptions,
  QuestionPaginator,
  CreateQuestionInput,
  CreateFeedbackInput,
  Feedback,
  CreateAbuseReportInput,
  WishlistQueryOptions,
  MyReportsQueryOptions,
  MyQuestionQueryOptions,
  GetParams,
  SettingsQueryOptions,
  TypeQueryOptions,
  Type,
  PaymentIntentCollection,
  CreateOrderPaymentInput,
  Card,
} from '@/types';
import { API_ENDPOINTS } from './endpoints';
import { HttpClient } from './http-client';
import { FollowedShopsQueryOptions } from '@/types';

class Client {
  products = {
    all: ({
      categories,
      tags,
      name,
      shop_id,
      price,
      ...query
    }: Partial<ProductQueryOptions> = {}) =>
      HttpClient.get<ProductPaginator>(API_ENDPOINTS.PRODUCTS, {
        searchJoin: 'and',
        with: 'shop',
        orderBy: 'updated_at',
        sortedBy: 'ASC',
        ...query,
        search: HttpClient.formatSearchParams({
          categories,
          tags,
          name,
          shop_id,
          price,
          status: 'publish',
        }),
      }),
    popular: (params: Partial<PopularProductsQueryOptions>) =>
      HttpClient.get<Product[]>(API_ENDPOINTS.PRODUCTS_POPULAR, {
        with: 'shop',
        withCount: 'orders',
        ...params,
      }),
    get: ({ slug, language }: GetParams) =>
      HttpClient.get<Product>(`${API_ENDPOINTS.PRODUCTS}/${slug}`, {
        language,
        with: 'shop;tags;type',
        withCount: 'orders',
      }),
    download: (input: { product_id: string }) =>
      HttpClient.post<string>(API_ENDPOINTS.PRODUCTS_FREE_DOWNLOAD, input),
  };
  categories = {
    all: (query?: CategoryQueryOptions) =>
      HttpClient.get<CategoryPaginator>(API_ENDPOINTS.CATEGORIES, { ...query }),
  };
  tags = {
    all: (query?: QueryOptions) =>
      HttpClient.get<TagPaginator>(API_ENDPOINTS.TAGS, query),
    get: ({ slug, language }: { slug: string; language?: string }) =>
      HttpClient.get<Tag>(`${API_ENDPOINTS.TAGS}/${slug}`, { language }),
  };
  types = {
    all: (query?: TypeQueryOptions) =>
      HttpClient.get<Type[]>(API_ENDPOINTS.TYPES, { ...query }),
  };
  shops = {
    all: (query?: ShopQueryOptions) =>
      HttpClient.get<ShopPaginator>(API_ENDPOINTS.SHOPS, query),
    top: ({ name, ...query }: Partial<TopShopQueryOptions> = {}) =>
      HttpClient.get<ShopPaginator>(API_ENDPOINTS.TOP_SHOPS, {
        searchJoin: 'and',
        // withCount: 'products',
        ...query,
        search: HttpClient.formatSearchParams({
          name,
          is_active: 1,
        }),
      }),
    get: (slug: string) =>
      HttpClient.get<Shop>(`${API_ENDPOINTS.SHOPS}/${slug}`),
  };
  orders = {
    all: (query?: OrderQueryOptions) =>
      HttpClient.get<OrderPaginator>(API_ENDPOINTS.ORDERS, query),
    get: (tracking_number: string) =>
      HttpClient.get<Order>(`${API_ENDPOINTS.ORDERS}/${tracking_number}`),
    downloadable: (query?: OrderQueryOptions) =>
      HttpClient.get<OrderedFilePaginator>(
        API_ENDPOINTS.ORDERS_DOWNLOADS,
        query
      ),
    generateDownloadLink: (digital_file_id: string, name?: string) =>
      HttpClient.post<string>(
        API_ENDPOINTS.GENERATE_DOWNLOADABLE_PRODUCT_LINK,
        {
          digital_file_id,
        }
      ),
    verify: (data: CheckoutVerificationInput) =>
      HttpClient.post<VerifiedCheckoutResponse>(
        API_ENDPOINTS.ORDERS_CHECKOUT_VERIFY,
        data
      ),
    create: (data: CreateOrderInput) =>
      HttpClient.post<Order>(API_ENDPOINTS.ORDERS, data),
    getPaymentIntent: ({ tracking_number }: { tracking_number: string }) =>
      HttpClient.get<PaymentIntentCollection>(API_ENDPOINTS.PAYMENT_INTENT, {
        tracking_number,
      }),
    payment: (input: CreateOrderPaymentInput) =>
      HttpClient.post<any>(API_ENDPOINTS.ORDERS_PAYMENT, input),
    savePaymentMethod: (input: any) =>
      HttpClient.post<any>(API_ENDPOINTS.SAVE_PAYMENT_METHOD, input),
  };
  users = {
    me: () => HttpClient.get<User>(API_ENDPOINTS.USERS_ME),
    update: (user: UpdateProfileInput) =>
      HttpClient.put<User>(`${API_ENDPOINTS.USERS}/${user.id}`, user),
    login: (input: LoginUserInput) =>
      HttpClient.post<AuthResponse>(API_ENDPOINTS.USERS_LOGIN, input),
    register: (input: RegisterUserInput) =>
      HttpClient.post<AuthResponse>(API_ENDPOINTS.USERS_REGISTER, input),
    forgotPassword: (input: ForgetPasswordInput) =>
      HttpClient.post<PasswordChangeResponse>(
        API_ENDPOINTS.USERS_FORGOT_PASSWORD,
        input
      ),
    verifyForgotPasswordToken: (input: VerifyForgetPasswordTokenInput) =>
      HttpClient.post<PasswordChangeResponse>(
        API_ENDPOINTS.USERS_VERIFY_FORGOT_PASSWORD_TOKEN,
        input
      ),
    resetPassword: (input: ResetPasswordInput) =>
      HttpClient.post<PasswordChangeResponse>(
        API_ENDPOINTS.USERS_RESET_PASSWORD,
        input
      ),
    changePassword: (input: ChangePasswordInput) =>
      HttpClient.post<PasswordChangeResponse>(
        API_ENDPOINTS.USERS_CHANGE_PASSWORD,
        input
      ),
    logout: () => HttpClient.post<boolean>(API_ENDPOINTS.USERS_LOGOUT, {}),
  };
  questions = {
    all: ({ question, ...params }: QuestionQueryOptions) =>
      HttpClient.get<QuestionPaginator>(API_ENDPOINTS.PRODUCTS_QUESTIONS, {
        searchJoin: 'and',
        ...params,
        search: HttpClient.formatSearchParams({
          question,
        }),
      }),

    create: (input: CreateQuestionInput) =>
      HttpClient.post<Review>(API_ENDPOINTS.PRODUCTS_QUESTIONS, input),
  };
  feedback = {
    create: (input: CreateFeedbackInput) =>
      HttpClient.post<Feedback>(API_ENDPOINTS.PRODUCTS_FEEDBACK, input),
  };
  abuse = {
    create: (input: CreateAbuseReportInput) =>
      HttpClient.post<Review>(
        API_ENDPOINTS.PRODUCTS_REVIEWS_ABUSE_REPORT,
        input
      ),
  };
  reviews = {
    all: ({ rating, ...params }: ReviewQueryOptions) =>
      HttpClient.get<ReviewPaginator>(API_ENDPOINTS.PRODUCTS_REVIEWS, {
        searchJoin: 'and',
        with: 'user',
        ...params,
        search: HttpClient.formatSearchParams({
          rating,
        }),
      }),
    get: ({ id }: { id: string }) =>
      HttpClient.get<Review>(`${API_ENDPOINTS.PRODUCTS_REVIEWS}/${id}`),
    create: (input: CreateReviewInput) =>
      HttpClient.post<ReviewResponse>(API_ENDPOINTS.PRODUCTS_REVIEWS, input),
    update: (input: UpdateReviewInput) =>
      HttpClient.put<ReviewResponse>(
        `${API_ENDPOINTS.PRODUCTS_REVIEWS}/${input.id}`,
        input
      ),
  };
  wishlist = {
    all: (params: WishlistQueryOptions) =>
      HttpClient.get<ProductPaginator>(API_ENDPOINTS.USERS_WISHLIST, {
        with: 'shop',
        orderBy: 'created_at',
        sortedBy: 'desc',
        ...params,
      }),
    toggle: (input: { product_id: string }) =>
      HttpClient.post<{ in_wishlist: boolean }>(
        API_ENDPOINTS.USERS_WISHLIST_TOGGLE,
        input
      ),
    remove: (id: string) =>
      HttpClient.delete<Wishlist>(`${API_ENDPOINTS.WISHLIST}/${id}`),
    checkIsInWishlist: ({ product_id }: { product_id: string }) =>
      HttpClient.get<boolean>(
        `${API_ENDPOINTS.WISHLIST}/in_wishlist/${product_id}`
      ),
  };
  myQuestions = {
    all: (params: MyQuestionQueryOptions) =>
      HttpClient.get<QuestionPaginator>(API_ENDPOINTS.MY_QUESTIONS, {
        with: 'user',
        orderBy: 'created_at',
        sortedBy: 'desc',
        ...params,
      }),
  };
  myReports = {
    all: (params: MyReportsQueryOptions) =>
      HttpClient.get<QuestionPaginator>(API_ENDPOINTS.MY_REPORTS, {
        with: 'user',
        orderBy: 'created_at',
        sortedBy: 'desc',
        ...params,
      }),
  };
  follow = {
    shops: (query?: FollowedShopsQueryOptions) =>
      HttpClient.get<ShopPaginator>(API_ENDPOINTS.FOLLOWED_SHOPS, query),
    isShopFollowed: (input: { shop_id: string }) =>
      HttpClient.get<boolean>(API_ENDPOINTS.FOLLOW_SHOP, input),
    toggle: (input: { shop_id: string }) =>
      HttpClient.post<boolean>(API_ENDPOINTS.FOLLOW_SHOP, input),
    followedShopProducts: (params: Partial<FollowedShopsQueryOptions>) => {
      console.log(params);
      return HttpClient.get<Product[]>(API_ENDPOINTS.FOLLOWED_SHOPS_PRODUCTS, {
        ...params,
      });
    },
  };
  settings = {
    all: (params?: SettingsQueryOptions) =>
      HttpClient.get<Settings>(API_ENDPOINTS.SETTINGS, { ...params }),
    contactUs: (input: CreateContactUsInput) =>
      HttpClient.post<any>(API_ENDPOINTS.SETTINGS_CONTACT_US, input),
    upload: (input: File[]) => {
      let formData = new FormData();
      input.forEach((attachment) => {
        formData.append('attachment[]', attachment);
      });
      return HttpClient.post<Attachment[]>(API_ENDPOINTS.UPLOADS, formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
    },
  };
  cards = {
    all: (params?: any) =>
      HttpClient.get<Card[]>(API_ENDPOINTS.CARDS, { ...params }),
    remove: ({ id }: { id: string }) =>
      HttpClient.delete<any>(`${API_ENDPOINTS.CARDS}/${id}`),
    addPaymentMethod: (method_key: any) =>
      HttpClient.post<any>(API_ENDPOINTS.CARDS, method_key),
    makeDefaultPaymentMethod: (input: any) =>
      HttpClient.post<any>(API_ENDPOINTS.SET_DEFAULT_CARD, input),
  };
}

export default new Client();
