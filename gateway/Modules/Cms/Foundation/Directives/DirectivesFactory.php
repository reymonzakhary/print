<?php

namespace Modules\Cms\Foundation\Directives;
use Modules\Cms\Foundation\Directives\Cart\AddToCartDirective;
use Modules\Cms\Foundation\Directives\Cart\EmptyCartDirective;
use Modules\Cms\Foundation\Directives\Cart\RemoveFromCartDirective;

class DirectivesFactory
{
    /** 
     * @param string $directiveName
     * construct Directive object from it's class
    */
    public static function make(string $directiveName, $data)
    {
        return match ($directiveName) {
            'login' => (new LoginDirective($data)),
            'registration' => (new RegistrationDirective($data)),
            'logout' => (new LogoutDirective($data)),
            'emailVerification' => (new EmailVerificationDirective($data)),
            'addToCart' => (new AddToCartDirective($data)),
            'removeFromCart' => (new RemoveFromCartDirective($data)),
            'emptyCart' => (new EmptyCartDirective($data)),
            'createAddress' => (new CreateAddressDirective($data)),
            'updateAddress' => (new UpdateAddressDirective($data)),
            'deleteAddress' => (new DeleteAddressDirective($data)),
            'updateAccount' => (new UpdateAccountDirective($data)),
            'checkout' => (new CheckoutDirective($data)),
            default => null
        };
    }
}
