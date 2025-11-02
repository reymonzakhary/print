<?php

namespace App\Providers;

use App\Events\Produce\ChangeSystemItemStatusEvent;
use App\Events\Quotations\SupplierQuotationEvent;
use App\Events\SendPasswordEvent;
use App\Events\Tenant\Cart\RemoveFileEvent;
use App\Events\Tenant\Custom\BlueprintCustomProductsEvent;
use App\Events\Tenant\Report\CreateReportEvent;
use App\Events\Tenant\User\TenantCreateUserEvent;
use App\Events\Tenant\User\TenantDeleteUserEvent;
use App\Listeners\Categories\CategoryEventListener;
use App\Listeners\Categories\PriceGenerateEventListener;
use App\Listeners\DeliveryDays\DeliveryDayEventListener;
use App\Listeners\DesignTemplate\DesignTemplateEventListener;
use App\Listeners\FM\FMEventListener;
use App\Listeners\Notifications\MailListener;
use App\Listeners\Order\Item\ItemEventListener;
use App\Listeners\Order\OrderEventListener;
use App\Listeners\Order\QuotationEventListener;
use App\Listeners\PrintingMethods\PrintingMethodEventListener;
use App\Listeners\Produce\ChangeSystemItemStatusListener;
use App\Listeners\Produce\SendOrderToProducerListener;
use App\Listeners\Products\ProductEventListener;
use App\Listeners\PruneOldTokens;
use App\Listeners\Quotations\SupplierQuotationListener;
use App\Listeners\RevokeOldTokens;
use App\Listeners\SendPasswordListener;
use App\Listeners\System\ClientListener;
use App\Listeners\Tags\CreateTagListener;
use App\Listeners\Tenant\Blueprints\BlueprintEventListener;
use App\Listeners\Tenant\Cart\RemoveFileListener;
use App\Listeners\Tenant\Custom\BlueprintCustomProductsListener;
use App\Listeners\Tenant\Custom\BoxEventListener;
use App\Listeners\Tenant\Custom\BrandEventListener;
use App\Listeners\Tenant\Custom\CustomCategoryEventListener;
use App\Listeners\Tenant\Custom\CustomProductEventListener;
use App\Listeners\Tenant\Custom\OptionEventListener;
use App\Listeners\Tenant\PasswordChangedListener;
use App\Listeners\Tenant\Report\CreateReportListener;
use App\Listeners\User\CreateProfileListener;
use App\Listeners\User\DeleteProfileListener;
use App\Listeners\User\UnlinkUserAddressesListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Laravel\Passport\Events\AccessTokenCreated;
use Laravel\Passport\Events\RefreshTokenCreated;
use Modules\Campaign\Listeners\CampaignEventListener;
use Modules\Cms\Listeners\Resources\ResourceEventListener;

/**
 * Class EventServiceProvider
 * @package App\Providers
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        TenantCreateUserEvent::class => [
            CreateProfileListener::class,
        ],
        TenantDeleteUserEvent::class => [
            DeleteProfileListener::class,
            UnlinkUserAddressesListener::class
        ],
        AccessTokenCreated::class => [
            RevokeOldTokens::class
        ],
        RefreshTokenCreated::class => [
            PruneOldTokens::class
        ],
        SendPasswordEvent::class => [
            SendPasswordListener::class
        ],
        ChangeSystemItemStatusEvent::class => [
            ChangeSystemItemStatusListener::class
        ],
        BlueprintCustomProductsEvent::class => [
            BlueprintCustomProductsListener::class
        ],
        RemoveFileEvent::class => [
            RemoveFileListener::class
        ],
        CreateReportEvent::class => [
            CreateReportListener::class
        ],
        SupplierQuotationEvent::class => [
            SupplierQuotationListener::class
        ]
        /*'Illuminate\Mail\Events\MessageSending' => [
            'App\Listeners\LogSentMessage',
        ],*/
    ];

    protected $subscribe = [
        QuotationEventListener::class,
        OrderEventListener::class,
        ItemEventListener::class,
        MailListener::class,
        ResourceEventListener::class,
        CampaignEventListener::class,
        DesignTemplateEventListener::class,
        FMEventListener::class,
        ProductEventListener::class,
        CategoryEventListener::class,
        PrintingMethodEventListener::class,
        DeliveryDayEventListener::class,
        PriceGenerateEventListener::class,
        SendOrderToProducerListener::class,
        CustomCategoryEventListener::class,
        CreateTagListener::class,
        BrandEventListener::class,
        BoxEventListener::class,
        OptionEventListener::class,
        CustomProductEventListener::class,
        BlueprintEventListener::class,
        ClientListener::class,
        PasswordChangedListener::class
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
