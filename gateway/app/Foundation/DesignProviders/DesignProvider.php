<?php

namespace App\Foundation\DesignProviders;

use Alexusmai\LaravelFileManager\Events\FilesUploaded;
use Alexusmai\LaravelFileManager\Events\FilesUploading;
use App\Events\Tenant\DesignTemplate\CreateTemplateEvent;
use App\Events\Tenant\FM\FinishedExtractingDesignProviderTemplate;
use App\Models\Tenant\DesignProvider as TenantsDesignProvider;
use App\Utilities\Traits\DesignProviders\DesignProviderValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\Tenant\DesignProviderTemplate;
use App\Repositories\DesignProviderTemplateRepository;
use App\Services\DesignProviders\ConneoPreflightService;
use Illuminate\Support\Facades\DB;

class DesignProvider
{
    use DesignProviderValidator;

    /**
     * @param Request $request
     * @param DesignProviderTemplateRepository $template
     * @param ConneoPreflightService $conneoService
    */
    public function __construct(
        protected Request $request,
        protected DesignProviderTemplateRepository $template,
        protected ConneoPreflightService $conneoService
    ){}

    /**
     * @param TenantsDesignProvider|null $provider
     * @return array
     * @throws ValidationException
     */
    public function validation(
        ?TenantsDesignProvider $provider
    ): array
    {
        $method = Str::camel($provider?->name).'Validations';

        if (in_array($method, get_class_methods($this))) {
            return $this->{$method}();
        }

        throw ValidationException::withMessages([
            'validation_rules' => __('Invalid validation rules.')
        ]);
    }

    /**
     * @param TenantsDesignProvider $provider
     * @return DesignProviderTemplateRepository|DesignProviderTemplate
     * @throws ValidationException
     */
    final public function store(
        TenantsDesignProvider $provider
    ): DesignProviderTemplateRepository|DesignProviderTemplate
    {
        $method = Str::camel($provider->name).'Store';

        if (in_array($method, get_class_methods($this))) {
            return $this->{$method}();
        }

        throw ValidationException::withMessages([
            'store_function' => __('Invalid store function.')
        ]);
    }

    /**
     *
    */
    private function conneoPreflightStore()
    {
        $designProviderTemplate = null;

        DB::transaction(function () use (&$designProviderTemplate) {

            $designProviderTemplate = $this->template->create([
                'name' => $this->request->name,
                'design_provider_id' => $this->request->design_provider_id,
                'external' => true
            ]);

            // send api request to conneo preflight external service
            $response = $this->conneoService->obtainSession([
                'sidesCount' => $this->request->sidesCount,
                'productSize' => $this->request->productSize,
                'productType' => $this->request->productType,
                'bleed' => $this->request->bleed,
                'quantity' => $this->request->quantity,
                'options' => $this->request->options,
                'myId' => $designProviderTemplate->id
            ]);

            $designProviderTemplate->update([
                'settings' => $response['data']
            ]);
        });

        return $designProviderTemplate;
    }

    /**
     *
    */
    private function prindustryDesignToolStore()
    {
        $designProviderTemplate = $this->template->create(
            $this->request->all()
        );

        if ($this->request->file('template')) {
            event(new FilesUploading($this->request));
            $designProviderTemplate->addMedia(
                $this->request->file('template'),
                "Providers/{$designProviderTemplate->designProvider->name}/templates/{$designProviderTemplate->name}",
                $this->request->input('overwrite'),
                $this->request->input('originalPath') . "Providers/{$designProviderTemplate->designProvider->name}/templates/{$designProviderTemplate->name}",
                'design-provider-templates',
                true,
                'tenancy'
            );
            event(new FilesUploaded($this->request));
        }
        if ($this->request->template_type === 'zip') {
            event(new CreateTemplateEvent($designProviderTemplate, request()->tenant->uuid, $this->request->user()));
        }
        if ($this->request->template_type === 'pdf') {
            event(new FinishedExtractingDesignProviderTemplate($designProviderTemplate));
        }

        return $designProviderTemplate;
    }

    /**
     * @param TenantsDesignProvider $provider
     * @param DesignProviderTemplate $template
    */
    public function update(
        TenantsDesignProvider $provider,
        DesignProviderTemplate $template
    )
    {
        $method = Str::camel($provider->name).'Update';

        if (in_array($method, get_class_methods($this))) {
            return $this->{$method}($template);
        }

        throw ValidationException::withMessages([
            'update_function' => __('Invalid update function.')
        ]);
    }
    /**
     * @param DesignProviderTemplate $template
    */
    private function conneoPreflightUpdate($template)
    {
        DB::transaction(function () use (&$template) {
            $template->update($this->request->all());

            $response = $this->conneoService->obtainProduct($template->settings['sessionId']);

            if ($response) {
                $template->update([
                    'properties' => ['product' => $response['data']]
                ]);
            }
        });

        return $template;
    }

    /**
     * @param DesignProviderTemplate $template
    */
    private function prindustryDesignToolUpdate($template)
    {
        $template->update($this->request->all());
        return $template;
    }
}
