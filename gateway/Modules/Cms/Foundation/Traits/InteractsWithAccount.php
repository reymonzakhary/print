<?php

namespace Modules\Cms\Foundation\Traits;

use Illuminate\Support\Facades\Validator;
use App\Models\Tenant\Address;
use Carbon\Carbon;

trait InteractsWithAccount
{

    public function createAddress()
    {

        $data = $this->request->except(['__data', '__command', '_token']);
        $callback = optional($this->request->__data)['callback_uri']??'/';

        $validationMessages = array_key_exists('validation_messages', $this->request->__data??[])? $this->request->__data['validation_messages']: [];
        $validationRules = array_key_exists('validation_rules', $this->request->__data??[])? $this->request->__data['validation_rules']: [];

        $validator = Validator::make($data, $validationRules, $validationMessages);

        if ($validator->fails()){
            return redirect($callback)->withInput()->withErrors($validator->errors());
        }

        if (!auth()->check()) {
            return redirect($callback);
        }

        $address = Address::firstOrCreate(
        [
            'address' => $this->request->get('address'),
            'number' => $this->request->get('number'),
            'city' => $this->request->get('city'),
            'region' => $this->request->get('region')
        ],[
            'zip_code' => $this->request->get('zip_code'),
        ]);

        auth()->user()->addresses()->syncWithoutDetaching([$address->id => [
            'type' => in_array($this->request->get('type'), ['invoice','home','work','primary','delivery','other'])? $this->request->get('type') : null,
            'full_name' => $this->request->get('full_name'),
            'company_name' => $this->request->get('company_name'),
            'phone_number' => $this->request->get('phone_number'),
            'tax_nr' => $this->request->get('tax_nr'),
            'default' => $this->accepted($this->request->get('default'))
        ]]);

        $this->refreshUserCache();
        return redirect($callback);
    }

    public function updateAddress()
    {

        $data = $this->request->except(['__data', '__command', '_token']);
        $callback = $this->request->__data['callback_uri'];

        $validationMessages = array_key_exists('validation_messages', $this->request->__data??[])? $this->request->__data['validation_messages']: [];
        $validationRules = array_key_exists('validation_rules', $this->request->__data??[])? $this->request->__data['validation_rules']: [];

        $validator = Validator::make($data, $validationRules, $validationMessages);

        if ($validator->fails()){
            return redirect($callback)->withInput()->withErrors($validator->errors());
        }

        if (!auth()->check()) {
            return redirect($callback);
        }

        $address = Address::where([
            ['address', $this->request->get('address')],
            ['number', $this->request->get('number')],
            ['city', $this->request->get('city')],
            ['region', $this->request->get('region')],
        ])->first();

        if ($address) {
            auth()->user()->addresses()->detach($this->request->address_id);
            auth()->user()->addresses()->syncWithoutDetaching([$address->id => [
                'type' => in_array($this->request->get('type'), ['invoice','home','work','primary','delivery','other'])? $this->request->get('type') : null,
                'full_name' => $this->request->get('full_name'),
                'company_name' => $this->request->get('company_name'),
                'phone_number' => $this->request->get('phone_number'),
                'tax_nr' => $this->request->get('tax_nr'),
                'default' => $this->accepted($this->request->get('default'))
            ]]);
        }

        if (auth()->user()->addresses()->detach($this->request->address_id)) {
            $address = Address::create([
                'address' => $this->request->get('address'),
                'number' => $this->request->get('number'),
                'city' => $this->request->get('city'),
                'region' => $this->request->get('region'),
                'zip_code' => $this->request->get('zip_code')
            ]);

            auth()->user()->addresses()->syncWithoutDetaching([$address->id => [
                'type' => in_array($this->request->get('type'), ['invoice','home','work','primary','delivery','other'])? $this->request->get('type') : null,
                'full_name' => $this->request->get('full_name'),
                'company_name' => $this->request->get('company_name'),
                'phone_number' => $this->request->get('phone_number'),
                'tax_nr' => $this->request->get('tax_nr'),
                'default' => $this->accepted($this->request->get('default'))
            ]]);
        }

        $this->refreshUserCache();
        return redirect($callback);
    }

    public function deleteAddress()
    {
        $callback = $this->request->__data['callback_uri'];

        if (!auth()->check()) {
            return redirect($callback);
        }

        auth()->user()->addresses()->detach($this->request->address_id);

        $this->refreshUserCache();
        return redirect($callback);
    }

    public function updateAccount()
    {
        $data = $this->request->except(['__data', '__command', '_token']);
        $callback = $this->request->__data['callback_uri'];

        $validationMessages = array_key_exists('validation_messages', $this->request->__data??[])? $this->request->__data['validation_messages']: [];
        $validationRules = array_key_exists('validation_rules', $this->request->__data??[])? $this->request->__data['validation_rules']: [];

        $validator = Validator::make($data, $validationRules, $validationMessages);

        if ($validator->fails()){
            return redirect($callback)->withInput()->withErrors($validator->errors());
        }

        if (!auth()->check()) {
            return redirect($callback);
        }

        auth()->user()->profile()->update([
            'first_name' => $this->request->get('first_name'),
            'last_name' => $this->request->get('last_name'),
            'gender' => $this->request->get('gender'),
            'dob' => Carbon::parse($this->request->get('dob')),
            'bio' => $this->request->get('bio'),
        ]);

        if ($this->request->hasFile('avatar')) {
            auth()->user()->profile->updateAvatar($this->request->file('avatar'));
        };

        $this->refreshUserCache();

        return redirect($callback);

    }
}
