<?php

namespace App\Blueprint\Services;

use App\Models\Tenant\CartVariation;
use Illuminate\Validation\ValidationException;

class BluePrintServices
{
    private $blueprint;
    private $cart;

    public function handle($request, CartVariation|null $cart, string $cart_id)
    {
        if (optional($request)['ResultsAction']) {
            return true;
        }

        $this->blueprint = $request->product->blueprints->first(fn($b) => $b->ns === ($request->resolution ?? ''));
        if (!$this->blueprint) {
            throw ValidationException::withMessages([
                'blueprint' => _('Wrong Blue print type => BluePrintServices')
            ]);
        }

        $this->cart = [
            'cart' => $cart,
            'cart_id' => $cart_id,
        ];
        if ($request['overwrite']) {
            $cart->media->map(fn($i) => $i->delete());
        }
        $start_step = $this->blueprint->pivot->step > 0 ? $this->blueprint->pivot->step : 1;
        $this->handleFlow(
            request: $request,
            step: $start_step
        );
    }

    public function handleFlow($request, $step = null)
    {
        if (optional($request)['ResultsAction']) {
            return true;
        }

        $step = $step ?? 1;
        $configuration = collect($this->blueprint->configuration)->first(fn($i) => ((int)$i['id'] === $step))['data'];
        $this->{$configuration['uses']}($request, $configuration[$configuration['uses']], $step, $configuration, $this->cart);
    }

    public function transitions($request, $data, $step, $node = null, $cart = null)
    {
        $classname = 'App\\Blueprint\\' . $data['mode'] . '\\' . $data['model'];
        if (class_exists($classname)) {
            app($classname)->handle($request, $data, $node, $cart);
        }
        $this->handleFlow($request, $step + 1);
    }

    public function actions($request, $data, $step, $node = null, $cart = null)
    {
        $classname = 'App\\Blueprint\\' . $data['mode'] . '\\' . $data['model'];
        if (class_exists($classname)) {
            app($classname)->handle($request, $data, $node, $cart);
        }
        $this->handleFlow($request, $step + 1);
    }

    public function events($request, $data, $step, $node = null, $cart = null)
    {

    }
}
