<?php

namespace PERP\User\Responses\Resource;

use Illuminate\Http\Resources\Json\JsonResource;
use PERP\RateMyTenant\Principal\Responses\Resource\PrincipalResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $businessInfo = [];

        $roles = $this->roles ? $this->roles->pluck('system_name')->toArray() : [];

        $meta = !is_array($this->meta) ? json_decode($this->meta, true) : $this->meta;

        $userInfo = [
            'id'   => $this->id,
            'roles' => $roles,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'date_of_birth' => $this->date_of_birth,
            'mobile_number' => $this->mobile_number,

            'address_line' => $this->address_line,
            'city' => $this->city,
            'state' => $this->state,
            'post_code' => $this->post_code,
            'country' => $this->country,
            'timezone' => $this->timezone,

            'profile_picture' => $this->profile_picture,
            'avatar_src' => $this->profile_picture ? config('PERP.domain_assets') . "/avatars/" . $this->profile_picture : "",

            'two_factor_secret' => $this->two_factor_secret,

            'two_factor_enabled' => $this->two_factor_enabled,
            'verified_at' => $this->verified_at,
            'profile_completed_at' => $this->profile_completed_at,
            'skip_complete_profile' => $this->skip_complete_profile,
            'mobile_added_at' => $this->mobile_added_at,
            'mobile_verified_at' => $this->mobile_verified_at,
            'create_new_password' => $this->create_new_password,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'meta' => $meta,
        ];

        if (in_array('principal', $roles)) {
            $principal = $this->principal ? $this->principal : null;
        } else if (in_array('pm', $roles)) {
            $pm = $this->pm ? $this->pm : null;
            $principal = isset($pm->principal) ? $pm->principal : null;
        }

        $business = isset($principal->business) ? $principal->business : null;

        $filename = isset($business) ? $business->custom_username . ".pdf" : "";
        $subscription = isset($business) ? ($business->subscription != null ? $business->subscription->whereIn('stripe_status', ['trialing', 'active'])->all() : null) : null;

        $businessInfo = [
            'principal_id' => isset($principal) ? $principal->id : '',

            'business_id' => isset($principal) ? $principal->business_id : '',
            'business_name' => isset($business) ? $business->business_name : '',
            'business_email_address' => isset($business) ? $business->business_email_address : '',
            'business_website' => isset($business) ? $business->business_website : '',
            'business_logo' => isset($business) ? $business->business_logo : '',
            'business_logo_src' => isset($business) && $business->business_logo ? config('PERP.domain_assets') . "/logos/" . $business->business_logo : "",
            'business_color' => isset($business) ? $business->business_color : '',
            'business_text_color' => isset($business) ? $business->business_text_color : '',
            'business_mobile_number' => isset($business) ?  $business->business_mobile_number : '',
            'business_phone_number' => isset($business) ?  $business->business_phone_number : '',

            'abn' => isset($business) ? $business->ABN : '',
            'acn' => isset($business) ? $business->ACN : '',

            'business_address_line' => isset($business) ? $business->address_line : '',
            'business_city' => isset($business) ? $business->city : '',
            'business_state' => isset($business) ? $business->state : '',
            'business_post_code' => isset($business) ? $business->post_code : '',
            'business_country' => isset($business) ? $business->country : '',

            'subscription' => isset($subscription) && $subscription != null ? $subscription : [],

            'custom_username' => isset($business) ?  $business->custom_username : '',
            'custom_link' => isset($business) ?  $business->custom_link : '',
            'review_link' => isset($business) ?  $business->review_link : '',

            'marketing_pdf_src' => isset($business) && $business->custom_link ?  config('PERP.domain_assets') . "/marketing/" . $filename : "",

            'business_status' => isset($business) ?  $business->status : '',
            'abn_status' => isset($business) ?  $business->abn_status : '',
            'abn_lookup' => isset($business) ?  $business->abn_lookup : '',

            'approved_by' => isset($business) ?  $business->approved_by : '',
            'approved_date' => isset($business) ?  $business->approval_date : '',
        ];

        return array_merge($userInfo, $businessInfo);
    }
}
