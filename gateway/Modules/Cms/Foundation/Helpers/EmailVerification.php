<?php

namespace Modules\Cms\Foundation\Helpers;

use App\Mail\Tenant\Auth\VerificationMail;
use App\Models\Tenant\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Modules\Cms\Foundation\Contracts\SnippetContract;
use Modules\Cms\Foundation\Traits\HasCustomHtmlAttributes;
use Modules\Cms\Foundation\Traits\HasDirectives;
use Modules\Cms\Foundation\Traits\IsGeneralSnippet;

class EmailVerification extends SnippetContract
{
    use HasDirectives, HasCustomHtmlAttributes, IsGeneralSnippet;

    public function verify()
    {
        $data = $this->request->except(['__data', '__command', '_token']);

        // validation step
        $validationMessages = array_key_exists('validation_messages', $this->request->__data??[])? optional($this->request->__data)['validation_messages']: [];
        $validationRules = array_key_exists('validation_rules', $this->request->__data??[])? optional($this->request->__data)['validation_rules']: [];

        $validator = Validator::make($data, $validationRules, $validationMessages);

        if ($validator->fails()) {
            return optional($this->request->__data)['onErrorRedirect']?
                redirect(optional($this->request->__data)['onErrorRedirect'])->withInput()->withErrors($validator->errors()):
                redirect('/')->withInput()->withErrors($validator->errors());
        }

        // verification step
        $email = decrypt(session()->get('verification_email'));

        $user = User::where([
            ['email', $email],
            ['remember_token', $this->request->code]
        ])->first();

        if (!$user) {
            return optional($this->request->__data)['onErrorRedirect']?
                redirect(optional($this->request->__data)['onErrorRedirect'])->withInput()->withErrors($validator->errors()):
                redirect('/')->withInput()->withErrors($validator->errors());
        }

        $user->update([
            'remember_token' => null,
            'email_verified_at' => now()
        ]);

        session()->forget('verification_email');

        return redirect($this->request->__data['onSuccessRedirect']?? '/');

    }

    public static function sendVerificationEmail(User $user)
    {
        $code = rand(100000, 9999999);
        $user->update(['remember_token' => $code]);
        Mail::to($user->email)->send(new VerificationMail($code));
    }

    public function sendEmail()
    {
        $data = $this->request->except(['__data', '__command', '_token']);

        // validation step
        $validationMessages = array_key_exists('validation_messages', $this->request->__data??[])? optional($this->request->__data)['validation_messages']: [];
        $validationRules = array_key_exists('validation_rules', $this->request->__data??[])? optional($this->request->__data)['validation_rules']: [];
        // dd($data, $validationRules, $validationMessages);
        $validator = Validator::make($data, $validationRules, $validationMessages);

        if ($validator->fails()) {
            return optional($this->request->__data)['onErrorRedirect']?
                redirect(optional($this->request->__data)['onErrorRedirect'])->withInput()->withErrors($validator->errors()):
                redirect('/')->withInput()->withErrors($validator->errors());
        }

        $user = User::where('email', $this->request->email)->first();

        if (!$user) {
            return optional($this->request->__data)['onErrorRedirect']?
                redirect(optional($this->request->__data)['onErrorRedirect'])->withInput()->withErrors($validator->errors()):
                redirect('/')->withInput()->withErrors($validator->errors());
        }

        $this->sendVerificationEmail($user);
        session()->put('verification_email', encrypt($this->request->email));

        return redirect($this->request->__data['onSuccessRedirect']?? '/');
    }

    /**
     * returns the
     *
     * @return void
     */
    public function getChunk()
    {
        $chunk = $this->emailChunk?? $this->verificationCodeChunk;
        $chunk = optional(DB::table('chunks')->where('name', $chunk)->first())->content;
        $chunk = $this->getDataFromHtmlCustomAttributes(htmlspecialchars_decode($chunk));
        return htmlspecialchars_decode($this->replaceChunkDirectives($chunk));
    }

}
