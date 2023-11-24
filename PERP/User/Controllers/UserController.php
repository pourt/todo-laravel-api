<?php

namespace PERP\User\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PERP\AccountPlan\Services\AccountPlanService;
use PERP\Log\Services\SystemLogService;
use PERP\Queues\GenerateMarketingPDFJob;
use PERP\Queues\SendEmailJob;
use PERP\RateMyTenant\Business\Services\BusinessLookupService;
use PERP\RateMyTenant\Business\Services\BusinessService;
use PERP\RateMyTenant\Principal\Services\PrincipalService;
use PERP\Setting\Services\SettingService;
use PERP\SMS\Queues\SendSMSJob;
use PERP\SMS\Services\SMSService;
use PERP\Stripe\Services\StripeService;
use PERP\Template\Mailable\EmailTemplate;
use PERP\Traits\ApiResponser;
use PERP\Url\Services\URLService;
use PERP\User\FormRequests\NewUserAccountRequest;
use PERP\User\Services\UserService;
use PERP\User\FormRequests\UpdateUserAccountRequest;
use PERP\User\FormRequests\UpdateUserProfileRequest;
use PERP\User\FormRequests\CompleteUserProfileRequest;
use PERP\User\FormRequests\AddMobileRequest;
use PERP\User\FormRequests\VerifyMobileRequest;
use PERP\User\Mailable\VerifyMobileNumberCode;
use PERP\User\Mailable\VerifyYourAccount;
use PERP\User\Models\User;
use PERP\User\Responses\Resource\UserResource;
use PERP\Utilities\Image\ImageService;

class UserController extends Controller
{
    use ApiResponser;

    public $templates;
    public $templateService;
    public $smsTemplate;
    public $emailTemplate;
    public $smsService;

    public $systemName = 'newUserAccount';

    public function __construct()
    {
    }

    public function index()
    {

    }

    public function find()
    {
        try {
            $user = (new UserService)->findUser();
        } catch (\Exception $e) {
            SystemLogService::exception($e);

            return $this->error($e->getMessage(), $e->getCode());
        }

        return $this->success(
            (new UserResource($user)),
            'User successfully retrieved'
        );
    }

    public function show()
    {
        try {
            $s0 = function ($query) {
                $query->where('id', request()->userId);
            };

            $user = (new UserService)->getUser($s0);
        } catch (\Exception $e) {
            SystemLogService::exception($e);

            return $this->error($e->getMessage(), $e->getCode());
        }

        return $this->success(
            (new UserResource($user)),
            'User successfully retrieved'
        );
    }

    public function store(NewUserAccountRequest $request)
    {
        DB::beginTransaction();

        $roles = ['member'];

        try {
            request()->merge([
                'verification_token' => uniqid() . uniqid(),
                'verification_code' => random_int(100000, 999999),
                'create_new_password' => true,
            ]);

            $user = (new UserService)->newUserAccount();
        } catch (\Exception $e) {

            DB::rollBack();

            return $this->error($e->getMessage(), $e->getCode());
        }


        DB::commit();

        return $this->success(
            (new NewMemberResource($user)),
            'User successfully created',
            201
        );
    }

    public function update(UpdateUserAccountRequest $request)
    {
        try {
            $s0 = function ($query) {
                $query->where('id', request()->userId);
            };
            $user = (new UserService)->getUser($s0);
        } catch (\Exception $e) {
            SystemLogService::exception($e);

            return $this->error($e->getMessage(), $e->getCode());
        }

        try {
            $user = (new UserService)->updateUserAccount($user);
        } catch (\Exception $e) {
            SystemLogService::exception($e);

            return $this->error($e->getMessage(), $e->getCode());
        }

        return $this->success(
            (new UserResource($user)),
            'User successfully modified'
        );
    }

    public function destroy()
    {
        try {
            $s0 = function ($query) {
                $query->where('id', request()->userId);
            };
            $user = (new UserService)->getUser($s0);
        } catch (\Exception $e) {
            SystemLogService::exception($e);

            return $this->error($e->getMessage(), $e->getCode());
        }

        try {
            $user = (new UserService)->deleteUserAccount($user);
        } catch (\Exception $e) {
            SystemLogService::exception($e);

            return $this->error($e->getMessage(), $e->getCode());
        }

        return $this->success(
            (new UserResource($user)),
            'User successfully removed'
        );
    }

    public function restore()
    {
        try {
            $s0 = function ($query) {
                $query->where("id", request()->userId);
            };

            $user = (new UserService)->getUser($s0, true);
        } catch (\Exception $e) {
            SystemLogService::exception($e);

            return $this->error($e->getMessage(), $e->getCode());
        }

        try {
            $user = (new UserService)->restoreUserAccount($user);
        } catch (\Exception $e) {
            SystemLogService::exception($e);

            return $this->error($e->getMessage(), $e->getCode());
        }

        return $this->success(
            (new UserResource($user)),
            'User successfully restored'
        );
    }

    public function enableTwoFactor(Request $request)
    {
        try {
            $userId = $request->user()->id;

            $twoFactorCode = (new UserService)->enableTwoFactor($request, $userId);
        } catch (\Exception $e) {
            SystemLogService::exception($e);

            return $this->error($e->getMessage(), $e->getCode());
        }

        return $this->success(
            [
                'two_factor_qr_code' => $twoFactorCode,
            ],
            'New account successfully created!',
            201
        );
    }

    public function uploadProfilePicture()
    {
        $filePath = config('PERP.upload_directory.user_profile');

        try {
            $imageFileName = (new ImageService)->uploadImage($filePath, 'image');
        } catch (\Exception $e) {
            SystemLogService::exception($e);

            return $this->error($e->getMessage(), $e->getCode());
        }

        try {
            $imageFile = (new ImageService)->retrieveImageData($filePath, $imageFileName);
        } catch (\Exception $e) {
            SystemLogService::exception($e);

            return $this->error($e->getMessage(), $e->getCode());
        }

        try {
            (new ImageService)->resize($imageFile, $filePath . '/thumbs//' . $imageFileName);
        } catch (\Exception $e) {
            SystemLogService::exception($e);

            return $this->error($e->getMessage(), $e->getCode());
        }

        try {
            request()->merge([
                'profile_picture' => $imageFileName
            ]);

            $user = (new UserService)->updateUserAccount(auth()->user());
        } catch (\Exception $e) {
            SystemLogService::exception($e);

            return $this->error($e->getMessage(), $e->getCode());
        }

        return $this->success(
            (new UserResource($user)),
            'Profile picture successfully uploaded'
        );
    }

    public function removeProfilePicture()
    {
        $user = auth()->user();
        try {
            request()->merge([
                'profile_picture' => ''
            ]);

            $user = (new UserService)->updateUserAccount($user);
        } catch (\Exception $e) {
            SystemLogService::exception($e);

            return $this->error($e->getMessage(), $e->getCode());
        }

        return $this->success(
            (new UserResource($user)),
            'Profile picture successfully removed'
        );
    }

    public function updateUserProfile(UpdateUserProfileRequest $request)
    {
        try {
            $user = auth()->user();

            if (preg_replace('/\s+/', '', $user->mobile_number) != preg_replace('/\s+/', '', request()->mobile_number)) {
                request()->merge([
                    'mobile_verified_at' => null
                ]);
            }

            $user = (new UserService)->updateUserAccount($user);
        } catch (\Exception $e) {
            SystemLogService::exception($e);

            return $this->error($e->getMessage(), $e->getCode());
        }

        return $this->success(
            (new UserResource($user)),
            'User successfully modified'
        );
    }

    public function completeProfile(CompleteUserProfileRequest $request)
    {
        DB::beginTransaction();

        $authUser = auth()->user();
        $roles = $authUser->roles->pluck('system_name')->toArray();

        request()->merge([
            'userId' => $authUser->id
        ]);

        if (in_array('principal', $roles) || in_array('pm', $roles)) {

            // Check if business is existing
            try {
                $businessRequest = [
                    'user_id' => $authUser->id,
                    'ABN' => request()->abn,
                    'ACN' => request()->acn,
                    'address_line' => request()->business_address_line,
                    'city' => request()->business_city,
                    'state' => request()->business_state,
                    'country' => request()->business_country,
                    'post_code' => request()->business_post_code,
                ];

                $whereBusiness = function ($query) {
                    $query->where('business_email_address', request()->business_email_address);
                };

                $business = (new BusinessService)->searchBusiness($whereBusiness);

                request()->merge($businessRequest);
                request()->request->remove('id');

                if (request()->abn) {
                    $abnLookup = (new BusinessLookupService())->abn(request()->abn)->lookup()->toArray();

                    $abnLookupData = [
                        'is_abn_verified' => isset($abnLookup['AbnStatus']) && strtolower($abnLookup['AbnStatus']) == 'active' ? true : false,
                        'abn_status' => isset($abnLookup['AbnStatus']) ? $abnLookup['AbnStatus'] : '',
                        'abn_lookup' => json_encode($abnLookup)
                    ];
                    request()->merge($abnLookupData);
                }

                if ($business) {
                    $business = (new BusinessService)
                        ->generateCustomUsername(request()->business_name)
                        ->generateCustomLink()
                        ->updateBusiness($business);
                } else {
                    $business = (new BusinessService)
                        ->generateCustomUsername(request()->business_name)
                        ->generateCustomLink()
                        ->createBusiness();
                }
            } catch (\Exception $e) {
                SystemLogService::exception($e);

                DB::rollBack();

                return $this->error($e->getMessage(), $e->getCode());
            }

            if (in_array('principal', $roles)) {
                try {
                    request()->only(['userId']);

                    $principal = (new PrincipalService)->getPrincipal();
                } catch (\Exception $e) {
                    SystemLogService::exception($e);

                    DB::rollBack();

                    return $this->error($e->getMessage(), $e->getCode());
                }

                request()->merge([
                    'business_id' => $business->id,
                ]);

                request()->request->remove('id');
                request()->request->remove('address_line');
                request()->request->remove('city');
                request()->request->remove('state');
                request()->request->remove('post_code');

                try {
                    $principal = (new PrincipalService)->updatePrincipal($principal);
                } catch (\Exception $e) {
                    SystemLogService::exception($e);

                    DB::rollBack();

                    return $this->error($e->getMessage(), $e->getCode());
                }
            }
        }

        try {
            request()->request->remove('id');

            $s0 = function ($query) {
                $query->where("id", auth()->user()->id);
            };
            $user = (new UserService)->getUser($s0);

            request()->merge([
                'profile_completed_at' => Carbon::now()->format('Y-m-d G:i:s')
            ]);

            if ($user->mobile_number != request()->mobile_number) {
                request()->merge([
                    'mobile_verified_at' => null
                ]);
            }

            // add business id on complete profile
            if (isset($business) && $business) {
                request()->merge([
                    'business_id' => $business->id
                ]);
            }

            $user = (new UserService)->updateUserAccount($user);
        } catch (\Exception $e) {
            SystemLogService::exception($e);

            DB::rollBack();

            return $this->error($e->getMessage(), $e->getCode());
        }



        if (in_array('principal', $roles) && $business->id) {
            try {
                // @todo use a service for this
                // @todo add pull of notification settings for default

                $notifications = [
                    'due_authorization',
                    'monthly_report'
                ];

                $where = function ($query) use ($notifications, $business) {
                    $query->whereIn('system_name', $notifications);
                    $query->where('setting_parameters', $business->id);
                };
                $getNotification = (new SettingService)->getNotifications($where);

                if (empty($getNotification->toArray())) {
                    (new SettingService)->setNotificationSettings(
                        $notifications,
                        User::class,
                        $authUser->id,
                        $business->id
                    );
                }
            } catch (\Exception $e) {
                SystemLogService::exception($e);

                Log::error($e->getMessage());
            }


            if (request()->do_checkout) {

                try {
                    $s1 = function ($query) {
                        $query->where('code', request()->plan_code);
                    };
                    $plan = (new AccountPlanService)->getAccountPlan($s1);
                } catch (\Exception $e) {
                    SystemLogService::exception($e);

                    DB::rollBack();

                    return $this->error($e->getMessage(), $e->getCode());
                }

                $address = [
                    'line1' => $user->address_line,
                    'city' => $user->city,
                    'state' => $user->state,
                    'country' => $user->country,
                    'postal_code' => $user->post_code,
                ];


                if (isset($business) && $business) {
                    $address = [
                        'line1' => $business->address_line,
                        'city' => $business->city,
                        'state' => $business->state,
                        'country' => $business->country,
                        'postal_code' => $business->post_code,
                    ];
                }

                $options = [
                    'address' => $address,
                    'name' => $user->fullname(),
                    'expand' => ['tax'],
                ];

                if (!$user->stripe_id) {
                    try {
                        $stripeCustomer  = $user->createOrGetStripeCustomer($options);
                        request()->merge([
                            'stripe_id' => $stripeCustomer->id
                        ]);
                        $user = (new UserService())->updateUserAccount($user);
                    } catch (\Exception $e) {
                        SystemLogService::exception($e);

                        DB::rollBack();

                        return $this->error($e->getMessage(), $e->getCode());
                    }
                }

                try {
                    $stripeCustomer = $user->asStripeCustomer();
                } catch (\Exception $e) {
                    SystemLogService::exception($e);

                    DB::rollBack();

                    return $this->error($e->getMessage(), $e->getCode());
                }

                try {
                    $lineItem = [
                        'price' => request()->plan_timeframe == 'monthly' ? $plan->monthly_pricing_key : $plan->annual_pricing_key,
                        'quantity' => 1
                    ];

                    $stripeService = (new StripeService);

                    $checkout = $stripeService
                        ->customer($user->stripe_id)
                        ->address($options['address'])
                        ->taxable(true)
                        ->checkoutMode('subscription')
                        ->trialPeriod(30)
                        ->addItem($lineItem)
                        ->checkout();
                } catch (\Exception $e) {
                    SystemLogService::exception($e);

                    DB::rollBack();

                    return $this->error($e->getMessage(), $e->getCode());
                }
            };

            DB::commit();

            try {
                $generateJob = (new GenerateMarketingPDFJob($business));
                dispatch($generateJob);
            } catch (\Exception $e) {
                SystemLogService::exception($e);

                Log::error($e->getMessage());
            }

            if (request()->do_checkout) {
                return $this->success(
                    [

                        'checkout' => [
                            'id' => $checkout->id,
                            'payment_intent' => $checkout->payment_intent,
                            'success' => $checkout->success_url,
                            'cancel' => $checkout->cancel_url,
                            'url' => $checkout->url,

                        ],
                        'plan' => $plan,
                        'user' => (new UserResource($user))
                    ],
                    'Profile succesfully completed'
                );
            }
        }

        DB::commit();

        return $this->success(
            (new UserResource($user)),
            'Profile succesfully completed'
        );
    }

    public function resendVerification()
    {
        try {
            $user = auth()->user();
            $roles = auth()->user()->roles->pluck('system_name')->toArray();
        } catch (\Exception $e) {
            SystemLogService::exception($e);

            return $this->error($e->getMessage(), $e->getCode());
        }

        try {
            $this->templates = $this->templateService->getTemplateBySystemName($this->systemName);
        } catch (\Exception $e) {
            SystemLogService::exception($e);

            return $this->error($e->getMessage(), $e->getCode());
        }

        try {

            $s0 = function ($query) {
                $query->where("id", auth()->user()->id);
            };
            $user = (new UserService)->getUser($s0);

            request()->merge([
                'userId' => $user->id,
                'verification_token' => uniqid() . uniqid(),
                'verification_code' => random_int(100000, 999999),
                'verified_at' => null,
            ]);
            $user = (new UserService)->updateUserAccount($user);
        } catch (\Exception $e) {
            SystemLogService::exception($e);

            return $this->error($e->getMessage(), $e->getCode());
        }

        try {
            $verifyLink = (new URLService())->getHttpOrigin() . "/" . config('PERP.links.verify_account') . "/" . $user->verification_token;
            $tinyUrl = (new URLService())->createShortUrl($verifyLink, auth()->user()->id, 'Verify Your Account')->data();
        } catch (\Exception $e) {
            SystemLogService::exception($e);

            return $this->error($e->getMessage(), $e->getCode());
        }

        try {
            /**
             * Prepare data to be parse by template
             */
            $userData = $user->toArray();
            //-------------------------------------------------//

            $mailData = array_merge([
                'app_name' => config('app.name'),
                'title' => 'Verify Account',
                'subject' => 'Verify Your Account',
                'button_text' => 'Verify Account',
                'url' => $verifyLink,
                'tiny_url' =>  $tinyUrl->tiny_url,
            ], $userData);
        } catch (\Exception $e) {
            SystemLogService::exception($e);

            return $this->error($e->getMessage(), $e->getCode());
        }

        $defaultMailDriver = new VerifyYourAccount($mailData);
        $this->sendEmail($user->email, $mailData, $defaultMailDriver);

        return $this->success(
            (new UserResource($user)),
            'Account verification request successfully sent'
        );
    }

    public function sendMobileVerification(AddMobileRequest $request)
    {
        try {
            $this->templates = $this->templateService->getTemplateBySystemName('sendMobileNumberVerification');
        } catch (\Exception $e) {
            SystemLogService::exception($e);

            return $this->error($e->getMessage(), $e->getCode());
        }

        try {
            $user = auth()->user();
        } catch (\Exception $e) {
            SystemLogService::exception($e);

            return $this->error($e->getMessage(), $e->getCode());
        }

        try {
            $newMobileNumber = preg_replace('/\s+/', '', request()->mobile_number);

            request()->merge([
                'verification_code' => random_int(100000, 999999),
                'mobile_added_at' => Carbon::now(),
                'mobile_verified_at' => $user->mobile_numnber == $newMobileNumber ? $user->mobile_verified_at : null
            ]);
            $user = (new UserService)->updateUserAccount($user);
        } catch (\Exception $e) {
            SystemLogService::exception($e);

            return $this->error($e->getMessage(), $e->getCode());
        }

        try {
            /**
             * Prepare data to be parse by template
             */

            $userData = $user->toArray();
            //-------------------------------------------------//

            $mailData = array_merge([
                'app_name' => config('app.name'),
                'title' => 'Verify Your Mobile Number',
                'subject' => 'Verify Your Account',
                'button_text' => 'Verify Account',
            ], $userData);
        } catch (\Exception $e) {
            SystemLogService::exception($e);

            return $this->error($e->getMessage(), $e->getCode());
        }

        $defaultTemplate = "Hi! Here is your mobile number verification code: [verification_code]. " . config('name');
        try {
            $mobile = preg_replace('/\s+/', '', $user->mobile_number);
            $this->sendSMS($mobile, $mailData, $defaultTemplate);
        } catch (\Exception $e) {
            SystemLogService::exception($e);

            return $this->error($e->getMessage(), $e->getCode());
        }

        return $this->success(
            (new UserResource($user)),
            'Account verification request successfully sent'
        );
    }

    public function verifyMobileNumber(VerifyMobileRequest $request)
    {
        try {
            $this->templates = $this->templateService->getTemplateBySystemName('verifyMobileNumber');
        } catch (\Exception $e) {
            SystemLogService::exception($e);

            return $this->error($e->getMessage(), $e->getCode());
        }

        try {
            $user = auth()->user();
            request()->merge([
                'verification_code' => null,
                'mobile_verified_at' => Carbon::now(),
            ]);
            $user = (new UserService)->updateUserAccount($user);
        } catch (\Exception $e) {
            SystemLogService::exception($e);

            return $this->error($e->getMessage(), $e->getCode());
        }


        try {
            /**
             * Prepare data to be parse by template
             */
            $userData = $user->toArray();
            $userData['user'] = $user->toArray();
            //-------------------------------------------------//

            $mailData = array_merge([
                'app_name' => config('app.name'),
                'subject' => 'Mobile number successfully linked to your account.',
            ], $userData);
        } catch (\Exception $e) {
            SystemLogService::exception($e);

            return $this->error($e->getMessage(), $e->getCode());
        }

        $defaultDriver = (new VerifyMobileNumberCode($mailData));
        $this->sendEmail($user->email, $mailData, $defaultDriver);

        return $this->success(
            (new UserResource($user)),
            'Account mobile successfully verified'
        );
    }

    private function sendEmail($emailAddress, $mailData, $defaultMailDriver)
    {
        $this->emailTemplate = $this->templates ?  $this->templates->where(function ($query) {
            $query->where('type', 'email');
            $query->where('use_for', 'templates');
        })->first() : null;

        if ($this->emailTemplate && $this->emailTemplate->body) {
            $content = $this->templateService->parseTemplate($this->emailTemplate->body, $mailData);

            $mailDriver = new EmailTemplate($this->emailTemplate, $content);
        } else {
            $mailDriver = $defaultMailDriver;
        }

        $emailJob = (new SendEmailJob($emailAddress, $mailDriver));
        dispatch($emailJob);
    }

    private function sendSMS($mobileNumber, $smsData, $defaultTemplate)
    {
        $this->smsTemplate = $this->templates ?  $this->templates->where(function ($query) {
            $query->where('type', 'sms');
            $query->where('use_for', 'templates');
        })->first() : null;

        $smsService = (new SMSService());
        if ($mobileNumber) {
            if ($this->smsTemplate) {
                $message = $this->templateService->parseTemplate($this->smsTemplate->body, $smsData);
            } else {
                $message = $this->templateService->parseTemplate($defaultTemplate, $smsData);
            }

            $smsBody = html_entity_decode(htmlspecialchars_decode(strip_tags($message)));
            $sendSMS = $smsService->sendMessage($mobileNumber, $smsBody);

            $smsJob = (new SendSMSJob($sendSMS));
            dispatch($smsJob);

            $smsService->logSMSDeliveryStatus('server');
        }
    }
}
