<?php


namespace App\Utilities;


use App\Models\Tenant\Passport\Token;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class ProxyRequest
{
    /**
     * @param string $email
     * @param string $password
     * @return mixed
     * @throws Exception
     */
    public function grantPasswordToken(
        string $email,
        string $password
    ): mixed
    {
        $params = [
            'grant_type' => 'password',
            'username' => $email,
            'password' => $password,
        ];

        return $this->makePostRequest($params);
    }

    /**
     * @param string|null $refreshToken
     *
     * @return mixed
     * @throws Exception
     */
    public function refreshAccessToken(
        ?string $refreshToken
    ): mixed
    {
        $refreshToken ??= request()->cookie('X-PRDTK');

        abort_unless($refreshToken, 403, __('Your refresh token is expired.'));

        $params = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ];

        Token::query()->where('user_id', request()->user()?->id)->delete();

        return $this->makePostRequest($params);
    }

    /**
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    protected function makePostRequest(
        array $params
    ): mixed
    {
        $params = array_merge([
            'client_id' => config('services.passport.password_client_id'),
            'client_secret' => config('services.passport.password_client_secret'),
            'scope' => '*',
        ], $params);

        $proxy = Request::create('oauth/token', 'post', $params);

        if (request()->headers->get('referer')) {
            $proxy->headers->set('referer', request()->headers->get('referer'));
            $proxy->headers->set('host', request()->headers->get('referer'));
        } else {
            $proxy->headers->set('referer', request()->headers->get('host'));
            $proxy->headers->set('host', request()->headers->get('host'));
        }
        $resp = json_decode(app()->handle($proxy)->getContent());
        if (optional($resp)->error || !optional($resp)->refresh_token) {
            return false;
        }
        $this->setHttpOnlyCookie($resp->refresh_token);
        return $resp;
    }

    /**
     * @param string $refreshToken
     */
    final protected function setHttpOnlyCookie(
        string $refreshToken
    ): void
    {
        cookie()->queue(
            'X-PRDTK',
            $refreshToken,
            1440, // 10 days
            null,
            null,
            App::isProduction(),
            true // httponly
        );
    }
}
