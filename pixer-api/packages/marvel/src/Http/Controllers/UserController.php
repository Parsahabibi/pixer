<?php

namespace Marvel\Http\Controllers;

use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Marvel\Database\Models\Product;
use Marvel\Database\Models\Shop;
use Newsletter;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Marvel\Database\Models\Profile;
use Marvel\Database\Models\User;
use Marvel\Database\Models\Wallet;
use Marvel\Database\Repositories\UserRepository;
use Marvel\Enums\Permission;
use Marvel\Exceptions\MarvelException;
use Marvel\Http\Requests\ChangePasswordRequest;
use Marvel\Http\Requests\UserCreateRequest;
use Marvel\Http\Requests\UserUpdateRequest;
use Marvel\Http\Resources\UserResource;
use Marvel\Mail\ContactAdmin;
use Marvel\Otp\Gateways\OtpGateway;
use Marvel\Traits\WalletsTrait;

class UserController extends CoreController
{
    use WalletsTrait;

    public $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }
    /**
     * Validate user email from the link sent to the user.
     * @param  $id
     * @param  $hash
     * @return RedirectResponse
     */
    public function verifyEmail($id, $hash): RedirectResponse
    {
        $user = User::findOrFail($id);
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403);
        }
        if ($user->hasVerifiedEmail()) {
            if($user->hasPermissionTo(Permission::SUPER_ADMIN) || $user->hasPermissionTo(Permission::STORE_OWNER)) {
                return Redirect::away(config('shop.dashboard_url'));
            }else{
                return Redirect::away(config('shop.shop_url'));
            }
        }
        $user->markEmailAsVerified();
        if($user->hasPermissionTo(Permission::SUPER_ADMIN) || $user->hasPermissionTo(Permission::STORE_OWNER)) {
            return Redirect::away(config('shop.dashboard_url'));
        }else{
            return Redirect::away(config('shop.shop_url'));
        }
    }
    /**
     * Send the email verification notification.
     *
     * @return JsonResponse
     */
    public function sendVerificationEmail(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->sendEmailVerificationNotification();
        return response()->json(['message' => 'Email verification link sent on your email id', 'success' => true]);
    }

    /**
     * Return the admins list
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */

    public function admins()
    {
        $admins = User::with('profile')
            ->where('is_active', true)
            ->whereHas('permissions', function ($query) {
                $query->where('name', Permission::SUPER_ADMIN);
            })->get();
        return UserResource::collection($admins);
    }



    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $limit = $request->limit ? $request->limit : 15;
        return $this->repository->with(['profile', 'address', 'permissions'])->paginate($limit);
    }

    /**
     * Store a newly created resource in storage.
     *Í
     * @param UserCreateRequest $request
     * @return bool[]
     */
    public function store(UserCreateRequest $request)
    {
        try {
            return $this->repository->storeUser($request);
        } catch (MarvelException $e) {
            throw new MarvelException(NOT_FOUND);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return array
     */
    public function show($id)
    {
        try {
            return $this->repository->with(['profile', 'address', 'shops', 'managed_shop'])->findOrFail($id);
        } catch (MarvelException $e) {
            throw new MarvelException(NOT_FOUND);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UserUpdateRequest $request
     * @param int $id
     * @return array
     */
    public function update(UserUpdateRequest $request, $id)
    {
        if ($request->user()->hasPermissionTo(Permission::SUPER_ADMIN)) {
            $user = $this->repository->findOrFail($id);
            return $this->repository->updateUser($request, $user);
        } elseif ($request->user()->id == $id) {
            $user = $request->user();
            return $this->repository->updateUser($request, $user);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return array
     */
    public function destroy($id)
    {
        try {
            return $this->repository->findOrFail($id)->delete();
        } catch (MarvelException $e) {
            throw new MarvelException(NOT_FOUND);
        }
    }

    public function me(Request $request)
    {
        try {
            $user = $request->user();

            if (isset($user)) {
                return $this->repository->with(['profile', 'wallet', 'address', 'shops.balance', 'managed_shop.balance'])->find($user->id);
            }
            throw new AuthorizationException(NOT_AUTHORIZED);
        } catch (MarvelException $e) {
            throw new MarvelException(NOT_AUTHORIZED);
        }
    }

    public function token(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->where('is_active', true)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return ["token" => null, "permissions" => []];
        }
        $email_verified= $user->hasVerifiedEmail();
        return ["token" => $user->createToken('auth_token')->plainTextToken, "permissions" => $user->getPermissionNames(), "email_verified" => $email_verified];
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return true;
        }
        return $request->user()->currentAccessToken()->delete();
    }

    public function register(UserCreateRequest $request)
    {
        $notAllowedPermissions = [Permission::SUPER_ADMIN];
        if ((isset($request->permission->value) && in_array($request->permission->value, $notAllowedPermissions)) || (isset($request->permission) && in_array($request->permission, $notAllowedPermissions))) {
            throw new AuthorizationException(NOT_AUTHORIZED);
        }
        $permissions = [Permission::CUSTOMER];
        if (isset($request->permission)) {
            $permissions[] = isset($request->permission->value) ? $request->permission->value : $request->permission;
        }
        $user = $this->repository->create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->givePermissionTo($permissions);
        $this->giveSignupPointsToCustomer($user->id);
        event(new Registered($user));
        return ["token" => $user->createToken('auth_token')->plainTextToken, "permissions" => $user->getPermissionNames()];
    }

    public function banUser(Request $request)
    {
        try {
            $user = $request->user();
            if ($user && $user->hasPermissionTo(Permission::SUPER_ADMIN) && $user->id != $request->id) {
                $banUser =  User::find($request->id);
                $banUser->is_active = false;
                $banUser->save();
                $this->inactiveUserShops($banUser->id);
                return $banUser;
            }
            throw new AuthorizationException(NOT_AUTHORIZED);
        } catch (MarvelException $th) {
            throw new MarvelException(SOMETHING_WENT_WRONG);
        }
    }
    function inactiveUserShops($userId)
    {
        $shops = Shop::where('owner_id', $userId)->get();
        foreach ($shops as $shop) {
            $shop->is_active = false;
            $shop->save();
            Product::where('shop_id', '=', $shop->id)->update(['status' => 'draft']);
        }
    }

    public function activeUser(Request $request)
    {
        try {
            $user = $request->user();
            if ($user && $user->hasPermissionTo(Permission::SUPER_ADMIN) && $user->id != $request->id) {
                $activeUser =  User::find($request->id);
                $activeUser->is_active = true;
                $activeUser->save();
                return $activeUser;
            }
            throw new AuthorizationException(NOT_AUTHORIZED);
        } catch (MarvelException $th) {
            throw new MarvelException(SOMETHING_WENT_WRONG);
        }
    }

    public function forgetPassword(Request $request)
    {
        $user = $this->repository->findByField('email', $request->email);
        if (count($user) < 1) {
            return ['message' => NOT_FOUND, 'success' => false];
        }
        $tokenData = DB::table('password_resets')
            ->where('email', $request->email)->first();
        if (!$tokenData) {
            DB::table('password_resets')->insert([
                'email' => $request->email,
                'token' => Str::random(16),
                'created_at' => Carbon::now()
            ]);
            $tokenData = DB::table('password_resets')
                ->where('email', $request->email)->first();
        }

        if ($this->repository->sendResetEmail($request->email, $tokenData->token)) {
            return ['message' => CHECK_INBOX_FOR_PASSWORD_RESET_EMAIL, 'success' => true];
        } else {
            return ['message' => SOMETHING_WENT_WRONG, 'success' => false];
        }
    }
    public function verifyForgetPasswordToken(Request $request)
    {
        $tokenData = DB::table('password_resets')->where('token', $request->token)->first();
        if (!$tokenData) {
            return ['message' => INVALID_TOKEN, 'success' => false];
        }
        $user = $this->repository->findByField('email', $request->email);
        if (count($user) < 1) {
            return ['message' => NOT_FOUND, 'success' => false];
        }
        return ['message' => TOKEN_IS_VALID, 'success' => true];
    }
    public function resetPassword(Request $request)
    {
        try {
            $request->validate([
                'password' => 'required|string',
                'email' => 'email|required',
                'token' => 'required|string'
            ]);

            $user = $this->repository->where('email', $request->email)->first();
            $user->password = Hash::make($request->password);
            $user->save();

            DB::table('password_resets')->where('email', $user->email)->delete();

            return ['message' => PASSWORD_RESET_SUCCESSFUL, 'success' => true];
        } catch (\Exception $th) {
            return ['message' => SOMETHING_WENT_WRONG, 'success' => false];
        }
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $user = $request->user();
            if (Hash::check($request->oldPassword, $user->password)) {
                $user->password = Hash::make($request->newPassword);
                $user->save();
                return ['message' => PASSWORD_RESET_SUCCESSFUL, 'success' => true];
            } else {
                return ['message' => OLD_PASSWORD_INCORRECT, 'success' => false];
            }
        } catch (\Exception $th) {
            throw new MarvelException(SOMETHING_WENT_WRONG);
        }
    }
    public function contactAdmin(Request $request)
    {
        try {
            $details = $request->only('subject', 'name', 'email', 'description');
            Mail::to(config('shop.admin_email'))->send(new ContactAdmin($details));
            return ['message' => EMAIL_SENT_SUCCESSFUL, 'success' => true];
        } catch (\Exception $e) {
            throw new MarvelException(SOMETHING_WENT_WRONG);
        }
    }

    public function fetchStaff(Request $request)
    {
        try {
            if (!isset($request->shop_id)) {
                throw new AuthorizationException(NOT_AUTHORIZED);
            }
            if ($this->repository->hasPermission($request->user(), $request->shop_id)) {
                return $this->repository->with(['profile'])->where('shop_id', '=', $request->shop_id);
            }
            throw new AuthorizationException(NOT_AUTHORIZED);
        } catch (MarvelException $e) {
            throw new MarvelException(SOMETHING_WENT_WRONG);
        }
    }

    public function staffs(Request $request)
    {
        $query = $this->fetchStaff($request);
        $limit = $request->limit ?? 15;
        return $query->paginate($limit);
    }

    public function socialLogin(Request $request)
    {
        $provider = $request->provider;
        $token = $request->access_token;
        $this->validateProvider($provider);

        try {
            $user = Socialite::driver($provider)->userFromToken($token);
            $userExist = User::where('email',  $user->email)->exists();

            $userCreated = User::firstOrCreate(
                [
                    'email' => $user->getEmail()
                ],
                [
                    'email_verified_at' => now(),
                    'name' => $user->getName(),
                ]
            );

            $userCreated->providers()->updateOrCreate(
                [
                    'provider' => $provider,
                    'provider_user_id' => $user->getId(),
                ]
            );

            $avatar = [
                'thumbnail' => $user->getAvatar(),
                'original' => $user->getAvatar(),
            ];

            $userCreated->profile()->updateOrCreate(
                [
                    'avatar' => $avatar
                ]
            );

            if (!$userCreated->hasPermissionTo(Permission::CUSTOMER)) {
                $userCreated->givePermissionTo(Permission::CUSTOMER);
            }

            if (empty($userExist)) {
                $this->giveSignupPointsToCustomer($userCreated->id);
            }

            return ["token" => $userCreated->createToken('auth_token')->plainTextToken, "permissions" => $userCreated->getPermissionNames()];
        } catch (\Exception $e) {
            throw new MarvelException(INVALID_CREDENTIALS);
        }
    }

    protected function validateProvider($provider)
    {
        if (!in_array($provider, ['facebook', 'google'])) {
            throw new MarvelException(PLEASE_LOGIN_USING_FACEBOOK_OR_GOOGLE);
        }
    }

    protected function getOtpGateway()
    {
        $gateway = config('auth.active_otp_gateway');
        $gateWayClass = "Marvel\\Otp\\Gateways\\" . ucfirst($gateway) . 'Gateway';
        return new OtpGateway(new $gateWayClass());
    }

    protected function verifyOtp(Request $request)
    {
        $id = $request->otp_id;
        $code = $request->code;
        $phoneNumber = $request->phone_number;
        try {
            $otpGateway = $this->getOtpGateway();
            $verifyOtpCode = $otpGateway->checkVerification($id, $code, $phoneNumber);
            if ($verifyOtpCode->isValid()) {
                return true;
            }
            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function sendOtpCode(Request $request)
    {
        $phoneNumber = $request->phone_number;
        try {
            $otpGateway = $this->getOtpGateway();
            $sendOtpCode = $otpGateway->startVerification($phoneNumber);
            if (!$sendOtpCode->isValid()) {
                return ['message' => OTP_SEND_FAIL, 'success' => false];
            }
            $profile = Profile::where('contact', $phoneNumber)->first();
            return [
                'message' => OTP_SEND_SUCCESSFUL,
                'success' => true,
                'provider' => config('auth.active_otp_gateway'),
                'id' => $sendOtpCode->getId(),
                'phone_number' => $phoneNumber,
                'is_contact_exist' => $profile ? true : false
            ];
        } catch (MarvelException $e) {
            throw new MarvelException(INVALID_GATEWAY);
        }
    }

    public function verifyOtpCode(Request $request)
    {
        try {
            if ($this->verifyOtp($request)) {
                return [
                    "message" => OTP_SEND_SUCCESSFUL,
                    "success" => true,
                ];
            }
            throw new MarvelException(OTP_VERIFICATION_FAILED);
        } catch (\Throwable $e) {
            throw new MarvelException(OTP_VERIFICATION_FAILED);
        }
    }

    public function otpLogin(Request $request)
    {
        $phoneNumber = $request->phone_number;

        try {
            if ($this->verifyOtp($request)) {
                // check if phone number exist
                $profile = Profile::where('contact', $phoneNumber)->first();
                $user = '';
                if (!$profile) {
                    // profile not found so could be a new user
                    $name = $request->name;
                    $email = $request->email;
                    if ($name && $email) {
                        $userExist = User::where('email',  $email)->exists();
                        $user = User::firstOrCreate([
                            'email'     => $email
                        ], [
                            'name'    => $name,
                        ]);
                        $user->givePermissionTo(Permission::CUSTOMER);
                        $user->profile()->updateOrCreate(
                            ['customer_id' => $user->id],
                            [
                                'contact' => $phoneNumber
                            ]
                        );
                        if (empty($userExist)) {
                            $this->giveSignupPointsToCustomer($user->id);
                        }
                    } else {
                        return ['message' => REQUIRED_INFO_MISSING, 'success' => false];
                    }
                } else {
                    $user = User::where('id', $profile->customer_id)->first();
                }

                return [
                    "token" => $user->createToken('auth_token')->plainTextToken,
                    "permissions" => $user->getPermissionNames()
                ];
            }
            return ['message' => OTP_VERIFICATION_FAILED, 'success' => false];
        } catch (\Throwable $e) {
            return response()->json(['error' => INVALID_GATEWAY], 422);
        }
    }

    public function updateContact(Request $request)
    {
        $phoneNumber = $request->phone_number;
        $user_id = $request->user_id;

        try {
            if ($this->verifyOtp($request)) {
                $user = User::find($user_id);
                $user->profile()->updateOrCreate(
                    ['customer_id' => $user_id],
                    [
                        'contact' => $phoneNumber
                    ]
                );
                return [
                    "message" => CONTACT_UPDATE_SUCCESSFUL,
                    "success" => true,
                ];
            }
            return ['message' => CONTACT_UPDATE_FAILED, 'success' => false];
        } catch (\Exception $e) {
            return response()->json(['error' => INVALID_GATEWAY], 422);
        }
    }

    public function addPoints(Request $request)
    {
        $request->validate([
            'points' => 'required|numeric',
            'customer_id' => ['required', 'exists:Marvel\Database\Models\User,id']
        ]);
        $points = $request->points;
        $customer_id = $request->customer_id;

        $wallet = Wallet::firstOrCreate(['customer_id' => $customer_id]);
        $wallet->total_points = $wallet->total_points + $points;
        $wallet->available_points = $wallet->available_points + $points;
        $wallet->save();
    }

    public function makeOrRevokeAdmin(Request $request)
    {
        $user = $request->user();
        if ($this->repository->hasPermission($user)) {
            $user_id = $request->user_id;
            try {
                $newUser = $this->repository->findOrFail($user_id);
                if ($newUser->hasPermissionTo(Permission::SUPER_ADMIN)) {
                    $newUser->revokePermissionTo(Permission::SUPER_ADMIN);
                    return true;
                }
            } catch (Exception $e) {
                throw new MarvelException(USER_NOT_FOUND);
            }
            $newUser->givePermissionTo(Permission::SUPER_ADMIN);

            return true;
        }

        throw new MarvelException(NOT_AUTHORIZED);
    }
    public function subscribeToNewsletter(Request $request)
    {
        try {
            $email = $request->email;
            Newsletter::subscribe($email);
            return true;
        } catch (\Throwable $th) {
            throw new MarvelException(SOMETHING_WENT_WRONG);
        }
    }
    public function updateUserEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
        ]);
        if ($validator->fails()) {
            throw new MarvelException($validator->errors()->first());
        }
       return $this->repository->updateEmail($request);
    }
}
