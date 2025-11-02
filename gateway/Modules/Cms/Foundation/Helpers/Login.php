<?php

namespace Modules\Cms\Foundation\Helpers;

use App\Models\Tenants\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Cms\Foundation\Contracts\SnippetContract;
use Modules\Cms\Foundation\Traits\HasCustomHtmlAttributes;
use Modules\Cms\Foundation\Traits\LoginHelperTrait;
use Modules\Cms\Foundation\Traits\HasDirectives;
use Modules\Cms\Foundation\Traits\IsGeneralSnippet;

class Login extends SnippetContract
{

    use HasDirectives, HasCustomHtmlAttributes, IsGeneralSnippet;

    /** 
     * @param void
     * @return HttpRedirect
    */
    public function login()
    {
        $data = $this->request->except(['__data', '__command', '_token']);

        $validationMessages = array_key_exists('validation_messages', $this->request->__data??[])? $this->request->__data['validation_messages']: [];
        $validationRules = array_key_exists('validation_rules', $this->request->__data??[])? $this->request->__data['validation_rules']: [];

        $validator = Validator::make($data, $validationRules, $validationMessages);
        
        if ($validator->fails()){
            return $this->request->__data['onErrorRedirect']?
                redirect($this->request->__data['onErrorRedirect'])->withInput()->withErrors($validator->errors()):
                redirect('/')->withInput()->withErrors($validator->errors());
        }

        if (optional($this->request->__data)['verified'] === 'true') {
            $user = User::where('email', $this->request->email)->first();
            if (!$user->email_verified_at) {
                EmailVerification::sendVerificationEmail($user);
                session()->put('verification_email', encrypt($this->request->email));
                return redirect(optional($this->request->__data)['onVerificationRedirect']?? '/');
            }
        }

        if (Auth::attempt(collect($data)->only('email', 'password')->toArray())) {
            return redirect(optional($this->request->__data)['onSuccessRedirect']?? '/home');
        }

        return redirect(optional($this->request->__data)['onErrorRedirect']??'/')->withErrors([[optional($this->request->__data)['loginFailureMessage']??'/'??'Faild to login.']]);
    }

    public function logout()
    {
        auth()->logout();
        return redirect(optional($this->request->__data)['onSuccessRedirect']??'/'?? '/home');
    }

    /** 
     * @param void
     * @return string
    */
    public function getChunk()
    {
        // get form from the chunk
        auth()->check()?
            $chunk = optional(DB::table('chunks')->where('name', $this->logoutChunk)->first())->content:
            $chunk = optional(DB::table('chunks')->where('name', $this->loginChunk)->first())->content;
        
        $chunk = $this->getDataFromHtmlCustomAttributes(htmlspecialchars_decode($chunk));

        return htmlspecialchars_decode($this->replaceChunkDirectives($chunk));
    }
}
