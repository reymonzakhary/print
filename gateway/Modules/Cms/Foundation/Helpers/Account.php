<?php

namespace Modules\Cms\Foundation\Helpers;

use App\Models\Tenant\Address;
use App\Models\Tenant\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Modules\Cms\Entities\Resource;
use Modules\Cms\Foundation\Compiler\SyntaxAnalyzer;
use Modules\Cms\Foundation\Contracts\SnippetContract;
use Modules\Cms\Foundation\Traits\HasCustomHtmlAttributes;
use Modules\Cms\Foundation\Traits\HasDirectives;
use Modules\Cms\Foundation\Traits\HasMedia;
use Modules\Cms\Foundation\Traits\HasRecursiveModels;
use Modules\Cms\Foundation\Traits\InteractsWithAccount;
use Modules\Cms\Foundation\Traits\IsGeneralSnippet;
use Modules\Cms\Transformers\Snippets\Account\UserResource;

class Account extends SnippetContract
{
    use IsGeneralSnippet, HasCustomHtmlAttributes, HasDirectives, HasRecursiveModels, HasMedia, InteractsWithAccount;

    public function __construct()
    {
        parent::__construct();

        $this->authUser = auth()->check()? UserResource::make($this->loadAuthUser()) : null; // load authenticated user
    }

    /**
     * returns the html content for the compiler to append
     *
     * @return string|null
     */
    public function getChunk(): string|null
    {
        $keys = explode('.', $this->_tagname);
        array_shift($keys);
        $obj = $this->getObject($this->authUser, $keys);

        if ($obj instanceof Model){
            $obj = [$obj];
        }

        if ($this->tpl && (is_array($obj) || $obj instanceof Collection)) {

            $this->data['callback_uri'] = '/'. request()->path();
            unset($this->data['authUser']);
            return (
                $this->render(
                    $this->replaceChunkDirectives(
                        $this->getDataFromHtmlCustomAttributes(
                            htmlspecialchars_decode($this->getChunkFromCacheOrDB($this->tpl)?->content??'')
                        )
                    )
                    , $obj
                )
            );
        }

        return $this->getObject($this->authUser, $keys);
    }

    protected function render($content, $iterable) {
        $html = '';

        foreach ($iterable as $value) {
            $parser = new SyntaxAnalyzer($value);
            $html .= $parser->injectModel($content)->parse()->getHtml();
        }

        return htmlspecialchars_decode($html);
    }

    protected function loadAuthUser(): ?User
    {
        return Cache::remember(
            tenant()->uuid.'.authUser',
            Carbon::now()->addMinutes(30),
            fn () => auth()->user()?->load('profile', 'addresses.country', 'settings', 'orders.items')
        );
    }

    protected function refreshUserCache()
    {
        Cache::forget(tenant()->uuid.'.authUser');
        $this->loadAuthUser();
    }
}
