<?php

namespace Modules\Cms\Foundation\Helpers;

use App\Models\Tenants\Team;
use App\Models\Tenants\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Cms\Foundation\Contracts\SnippetContract;
use Modules\Cms\Foundation\Traits\HasCustomHtmlAttributes;
use Modules\Cms\Foundation\Traits\HasDirectives;
use Modules\Cms\Foundation\Traits\HasValidation;
use Modules\Cms\Foundation\Traits\IsGeneralSnippet;

class Registration extends SnippetContract
{
    use HasDirectives, HasCustomHtmlAttributes, IsGeneralSnippet, HasValidation;

    protected array $rules = [
        'email' => 'required',
        'first_name' => 'required|string',
        'last_name' => 'required',
        'password' => 'required',
    ];

    /**
     * @param void
     * @return HttpResponse
     * this function is responsible for user registration, role assignment and team assignment
    */
    public function registration()
    {
        $data = $this->request->except(['__data', '__command', '_token']);

        $validationMessages = array_key_exists('validation_messages', $this->request->__data??[])? $this->request->__data['validation_messages']: [];
        $validationRules = array_key_exists('validation_rules', $this->request->__data??[])? $this->request->__data['validation_rules']: [];

        $validationRules = $this->prepareRules($validationRules);

        $validator = Validator::make($data, $validationRules, $validationMessages);

        if ($validator->fails()) {
            return optional($this->request->__data)['onErrorRedirect']?
                redirect($this->request->__data['onErrorRedirect'])->withInput()->withErrors($validator->errors()):
                redirect('/')->withInput()->withErrors($validator->errors());
        }

        // get roles
        $roles = explode(',', optional($this->request->__data)['roles']);
        $roles = Team::whereIn('name', $roles)->get('id')->map(fn ($item) => $item->id)->toArray();

        // get teams
        $teams = explode(',', optional($this->request->__data)['teams']);
        $teams = Team::whereIn('name', $teams)->get('id')->map(fn ($item) => $item->id)->toArray();

        // create new user
        $user = $this->createUser(
            $this->request->only('first_name', 'last_name', 'email', 'password', 'dob', 'avatar', 'bio'),
            $roles,
            $teams
        );

        // verify email if requires confirmation
        if (optional($this->request->__data)['emailVerification'] === 'true') {
            EmailVerification::sendVerificationEmail($user);
            session()->put('verification_email', encrypt($this->request->email));
        }

        return redirect($this->request->__data['onSuccessRedirect']?? '/');
    }

    /**
     * creates user with roles and teams
     *
     * @param array $data
     * @param array $roles
     * @param array $teams
     * @return User|null
     */
    public function createUser(array $data, array $roles, array $teams): User
    {
        $user = new User();

        DB::transaction(function () use ($data, $roles, $teams, &$user) {
            $user = User::create([
                'email' => optional($data)['email'],
                'password' => optional($data)['password']
            ]);

            $user->contexts()->sync([
                2 => [
                    'member' => true
                ]
            ]);

            $user->profile()->create($data);

            if ($roles) { $user->addRoles($roles); }

            if ($teams) { $user->userTeams()->syncwithoutdetaching($teams); }

        });
        return $user;
    }

    /**
     * @param void
     * @return string
    */
    public function getChunk()
    {
        $chunk = optional(DB::table('chunks')->where('name', $this->registrationChunk)->first())->content;
        $chunk = $this->getDataFromHtmlCustomAttributes(htmlspecialchars_decode($chunk));
        return htmlspecialchars_decode($this->replaceChunkDirectives($chunk));
    }

}
