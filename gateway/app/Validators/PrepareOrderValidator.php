<?php

namespace App\Validators;

use App\Enums\Status;
use App\Facades\Settings;
use App\Http\Requests\Order\OrderUpdateRequest;
use App\Models\Tenants\Address;
use App\Models\Tenants\Order;
use App\Models\Tenants\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

final class PrepareOrderValidator
{
    protected ?string $reference = NULL;
    protected ?string $order_nr = NULL;
    protected ?int $st = 300;
    protected ?string $st_message = NULL;
    protected ?string $message = NULL;
    protected ?int $price;
    protected ?bool $delivery_multiple = false;
    protected ?bool $delivery_pickup = false;

    protected ?int $address = NULL;
    protected ?int $invoice_address = NULL;
    protected ?string $invoice_address_type;
    protected ?string $invoice_address_full_name;
    protected ?string $invoice_address_company_name;
    protected ?string $invoice_address_tax_nr;
    protected ?string $invoice_address_phone_number;
    protected ?bool $invoice_team_address;
    protected ?int $invoice_team_id;
    protected ?string $invoice_team_name;

    protected ?string $address_type;
    protected ?string $address_full_name;
    protected ?string $address_company_name;
    protected ?string $address_tax_nr;
    protected ?string $address_phone_number;
    protected ?bool $address_team_address;
    protected ?int $address_team_id;
    protected ?string $address_team_name;

    protected ?int $shipping_cost = NULL;
    protected ?int $ctx_id = NULL;
    protected ?int $user_id = NULL;
    protected ?int $discount_id = NULL;
    protected ?string $note = NULL;
    protected ?int $updated_by;
    protected Model $order;
    protected OrderUpdateRequest $request;
    protected array $response;
    protected ?Carbon $expire_at;
    protected ?bool $archived;
    protected ?bool $editing;
    protected ?bool $locked;

    /**
     * PrepareOrderValidator constructor.
     * @param OrderUpdateRequest $request
     * @param Order              $order
     */
    public function __construct(
        OrderUpdateRequest $request,
        Order              $order
    )
    {
        $this->order = $order;
        $this->updated_by = $request->user()->id; // protected
        $this->request = $request;
        /** @var Order reference */
        $this->reference = $this->order->reference;
        $this->order_nr = $this->order->order_nr;
        $this->st = $this->order->st;
        $this->st_message = $this->order->st_message;
        $this->delivery_multiple = $this->order->delivery_multiple;
        $this->delivery_pickup = $this->order->delivery_pickup;

        /** @var Address $address */
        $address = $this->order->delivery_address()->first();
        $this->address = $address?->id;
        $this->address_type = $address?->pivot?->type;
        $this->address_full_name = $address?->pivot?->full_name;
        $this->address_company_name = $address?->pivot?->company_name;
        $this->address_tax_nr = $address?->pivot?->tax_nr;
        $this->address_phone_number = $address?->pivot?->phone_number;
        $this->address_team_address = $address?->pivot?->team_address;
        $this->address_team_id = $address?->pivot?->team_id;
        $this->address_team_name = $address?->pivot?->team_name;

        /** @var Address $invoice_address */
        $invoice_address = $this->order->invoice_address()->first();
        $this->invoice_address = $invoice_address?->id;
        $this->invoice_address_type = $invoice_address?->pivot->type;
        $this->invoice_address_full_name = $invoice_address?->pivot->full_name;
        $this->invoice_address_company_name = $invoice_address?->pivot->company_name;
        $this->invoice_address_tax_nr = $invoice_address?->pivot->tax_nr;
        $this->invoice_address_phone_number = $invoice_address?->pivot->phone_number;
        $this->invoice_team_address = $invoice_address?->pivot->team_address;
        $this->invoice_team_id = $invoice_address?->pivot->team_id;
        $this->invoice_team_name = $invoice_address?->pivot->team_name;



        $this->shipping_cost = $this->order->shipping_cost;
        $this->note = $this->order->note;
        $this->message = $this->order->message;
        $this->ctx_id = $this->order->ctx_id;
        $this->user_id = $this->order->user_id;
        $this->discount_id = $this->order->discount_id;
        $this->price = $this->order->price->amount();
        $this->expire_at = $this->order->expire_at;

        $this->archived = $this->order->archived;
        $this->editing = $this->order->getAttribute('editing');
        $this->locked = $this->order->getAttribute('locked');
    }

    /**
     * order reference to pushed to the client
     *
     * @return string|null
     */
    final public function reference(): ?string
    {
        if ($reference = $this->request->get('reference')) {
            return $this->reference = $reference;
        }
        return $this->reference;
    }

    /**
     * @return int|null
     */
    final public function price(): ?int
    {
        $this->price = $this->request->get('price');
        return $this->price;
    }

    /**
     * @return int|null
     */
    final public function expire_at(): ?Carbon
    {
        $this->expire_at = Carbon::now()->addDays($this->request->get('expire_at'));

        return $this->expire_at;
    }

    /**
     * @return string|null
     */
    final public function order_nr(): ?string
    {
        return $this->order_nr;
    }

    /**
     * @return int|null
     * @throws ValidationException
     */
    final public function st(): ?int
    {
        if ($this->order_nr && $this->request->get('st') === Status::DRAFT->value) {
            throw ValidationException::withMessages([
                'status' => __("Cannot update order status to be Draft it should be editing")
            ]);
        }

        if ($this->st === Status::DRAFT->value && $this->request->get('st') === Status::NEW->value) {
            collect($this->order->items)->map(function ($item) {
                if ($this->order->delivery_multiple) {
                    if ($item->delivery_separated && $item->children->count() === 0) {

                        throw ValidationException::withMessages(['addresses' =>
                            __("Item {$item->id} must have addresses")
                        ]);

                    } elseif (!$item->delivery_separated && $item->addresses->count() === 0) {
                        throw ValidationException::withMessages(['address' =>
                            __("Item {$item->id} must have address")
                        ]);
                    }
                }
            });
        }

        return $this->st = $this->request->get('st');
    }

    /**
     * @return mixed
     * @throws ValidationException
     */
    final public function archived()
    {
        if($this->archived === Status::ARCHIVED->value && $this->request->archived !== Status::ARCHIVED->value) {
            throw ValidationException::withMessages([
                "status" => __("We can't update order, is already archived.")
            ]);
        }

        if ($this->request->archived && !($this->st == Status::DONE->value || $this->st == Status::CANCELED->value)) {
            throw ValidationException::withMessages([
                "status" => __("order can't be archived until it's completed or canceled.")
            ]);
        }

        return $this->request->archived;
    }

    /**
     * @return string|null
     * @throws ValidationException
     */
    final public function st_message(): ?string
    {
        if ($this->st === Status::REJECTED->value && !$this->request->get('st_message')) {
            throw ValidationException::withMessages(['st_message' =>
                __("Status message is required.")
            ]);
        }

        return $this->st_message = $this->request->get('st_message');
    }

    /**
     * @return string|null
     */
    final public function message(): ?string
    {
        if ($this->request->get('message')) {
            $this->message = $this->request->get('message');
        }

        return $this->st_message;
    }

    /**
     * @return int|null
     */
    final public function discount_id(): ?int
    {
        $this->discount_id = $this->request->discount_id;
        return $this->discount_id;
    }

    /**
     * @return int|null
     * @throws ValidationException
     */
    final public function ctx_id(): ?int
    {
        // check if user in request
        if (is_null($this->request->user_id) && optional($this->order)->orderedBy !== null) {
            $this->user_id = NULL;
            $this->address = NULL;
        }
        if (is_null($this->request->address)) {
            if ($this->address !== null) {
                $this->address = NULL;
            }
        }

        $this->ctx_id = $this->request->ctx_id;
        return $this->ctx_id;
    }

    /**
     * @return int|null
     * @throws ValidationException
     */
    final public function user_id(): ?int
    {
        if (is_null($this->ctx_id)) {
            throw ValidationException::withMessages(['ctx_id' =>
                __('The ctx id is required.')
            ]);
        }

        if ($user = User::where('id', $this->request->user_id)->first()) {
            if ($this->request->get('ctx_id')) {
                if (!$user->contexts()->where('contexts.id', $this->request->ctx_id)->exists()) {
                    throw ValidationException::withMessages(['user_id' =>
                        __('The selected user id is not belong to selected context.')
                    ]);
                }
            } else {
                if (!$user->contexts()->where('contexts.id', $this->ctx_id)->exists()) {
                    $this->ctx_id = $user->contexts()->first()?->id;
                    if($this->ctx_id !== NULL){
                        $this->order->update(['ctx_id' => $user->contexts()->first()->id]);
                    }else {
                        throw ValidationException::withMessages(['user_id' =>
                            __('The selected user id is not belong to existing context.')
                        ]);
                    }
                }
            }
        } else {
            throw ValidationException::withMessages(['user_id' =>
                __('The selected user id is invalid.')
            ]);
        }

        $this->user_id = $this->request->get('user_id');

        if (is_null($this->request->address)) {
            $address = $user->userTeams->reject(function ($team) {
                return !$team->address()->where('addresses.id', $this->address)->exists();
            })->map(function ($team) {
                return $team->address()->where('addresses.id', $this->address)->exists();
            })->first() | $user
                    ->addresses()
                    ->where('addresses.id', $this->address)
                    ->exists();
            if (!$address) {
                $this->address = NULL;
                $this->invoice_address = NULL;
            }
        }

        return $this->user_id;
    }

    /**
     * @return bool|null
     * @throws ValidationException
     */
    final public function delivery_multiple(): ?bool
    {
        $this->delivery_multiple = is_null($this->request->get('delivery_multiple'))
            ? $this->delivery_multiple
            : $this->request->get('delivery_multiple');

        if ($this->delivery_multiple) {
            $this->address = NULL;
            $this->delivery_pickup = NULL;
        }else{
            $this->order->items()->each(function ($item) {
                $item->children()->each(function ($item) {
                    $item->addresses()->detach();
                });
                $item->addresses()->detach();
            });
        }

        $this->delivery_pickup();
        return $this->delivery_multiple;
    }

    /**
     * @return bool|null
     * @throws ValidationException
     */
    final public function delivery_pickup(): ?bool
    {
        if (!$this->delivery_multiple) {
            $this->delivery_pickup = is_null($this->request->get('delivery_pickup')) ?
                $this->delivery_pickup :
                $this->request->get('delivery_pickup');

            if ($this->delivery_pickup) {
                $this->address = NULL;
            }
        }
        $this->address();
        return $this->delivery_pickup;
    }

    /**
     * @return int|null
     */
    final public function shipping_cost(): ?int
    {
        return $this->shipping_cost = $this->request->get('shipping_cost');
    }

    /**
     * @return string|null
     */
    final public function note(): ?string
    {
        return $this->note = $this->request->get('note');
    }

    /**
     * @return int|null
     * @throws ValidationException
     */
    final public function address(): ?int
    {
        if ($this->delivery_multiple || is_null($this->request->address)) {
            $this->invoice_address();

            return $this->address = null;
        }

        if ($this->delivery_pickup) {
            if (!$address = $this->order
                ->context
                ?->addresses()
                ->where('addresses.id', $this->request->get('address'))
                ->first()
            ) {
                throw ValidationException::withMessages([
                    'address' =>
                        __('The selected Address is not related to the order context.')
                ]);
            }

            $this->address_type = 'pickup';

            $this->address_team_address = false;
            $this->address_team_id = null;
            $this->address_team_name = null;
        } else {
            if (is_null($this->request->user_id)) {
                if ($user = $this->order->orderedBy) {
                    $address = $user->userTeams->reject(function ($team) {
                        return !$team->address()->where('addresses.id', $this->request->address)->exists();
                    })->map(function ($team) {
                        return $team->address()->where('addresses.id', $this->request->address)->exists();
                    })->first();

                    if (Settings::useTeamAddress()?->value) {
                        if (!$address) {
                            throw ValidationException::withMessages([
                                'address' =>
                                    __('The selected Address is not related to the existing team where the user in it.')
                            ]);
                        }
                    } else {
                        if (!$user
                                ->addresses()
                                ->where('addresses.id', $this->request->address)
                                ->exists() && !$address) {
                            throw ValidationException::withMessages([
                                'address' =>
                                    __('The selected Address is not related to the existing user.')
                            ]);
                        }
                    }
                } else {
                    throw ValidationException::withMessages([
                        'user_id' =>
                            __('The user id is required.')
                    ]);
                }
            } else {
                if ($user = User::where('id', $this->request->user_id)->first()) {
                    $address = $user->userTeams->reject(function ($team) {
                        return !$team->address()->where('addresses.id', $this->request->address)->exists();
                    })->map(function ($team) {
                        return $team->address()->where('addresses.id', $this->request->address)->exists();
                    })->first();
                    if (Settings::useTeamAddress()?->value) {
                        if (!$address) {
                            throw ValidationException::withMessages([
                                'address' =>
                                    __('The selected Address is not related to the existing team where the user in it.')
                            ]);
                        }
                    } else {
                        if (!$user
                                ->addresses()
                                ->where('addresses.id', $this->request->address)
                                ->exists() && !$address) {
                            throw ValidationException::withMessages([
                                'address' =>
                                    __('The selected Address is not related to the selected user id.')
                            ]);
                        }
                    }
                } else {
                    throw ValidationException::withMessages([
                        'user_id' =>
                            __('The user id is required.')
                    ]);
                }
            }

            if (!Settings::useTeamAddress()?->value) {
                $address = $user->addresses()->where('addresses.id', $this->request->address)->first();
                if (!$address) {
                    $address = $user->userTeams->reject(function ($team) {
                        return !$team->address()->where('addresses.id', $this->request->address)->first();
                    })->map(function ($team) {
                        return $team->address()->where('addresses.id', $this->request->address)->first();
                    })->first();
                }
            } else {
                $address = $user->userTeams->reject(function ($team) {
                    return !$team->address()->where('addresses.id', $this->request->address)->first();
                })->map(function ($team) {
                    return $team->address()->where('addresses.id', $this->request->address)->first();
                })->first();
            }
            $this->address = $this->request->address;
            $this->address_type = "delivery";

            $this->address_team_address = $address->pivot->team_address;
            $this->address_team_id = $address->pivot->pivotParent->id;
            $this->address_team_name = $address->pivot->pivotParent->name;

            $this->invoice_address();
        }



        $this->address_company_name = $address->pivot->company_name;
        $this->address_full_name = $address->pivot->full_name;
        $this->address_tax_nr = $address->pivot->tax_nr;
        $this->address_phone_number = $address->pivot->phone_number;

        return $this->address;
    }

    /**
     * @return int|null
     * @throws ValidationException
     */
    final public function invoice_address(): ?int
    {
        $invoice_address = $this->address ?? $this->invoice_address;
        if ($this->request->invoice_address) {
            $invoice_address = $this->request->invoice_address;
        }

        if (is_null($invoice_address)) {
            return $this->invoice_address;
        }


        if (is_null($this->request->user_id)) {
            if ($user = $this->order->orderedBy) {
                $address = $user->userTeams->reject(function ($team) use ($invoice_address) {
                    return !$team->address()->where('addresses.id', $invoice_address)->exists();
                })->map(function ($team) use ($invoice_address) {
                    return $team->address()->where('addresses.id', $invoice_address)->exists();
                })->first();

                if (Settings::useTeamAddress()?->value) {
                    if (!$address) {
                        throw ValidationException::withMessages(['address' =>
                            __('The selected invoice Address is not related to the existing team where the user in it.')
                        ]);
                    }
                } else {
                    if (!$user
                            ->addresses()
                            ->where('addresses.id', $invoice_address)
                            ->exists() && !$address) {
                        throw ValidationException::withMessages(['address' =>
                            __('The selected invoice Address is not related to the existing user.')
                        ]);
                    }
                }

            } else {
                throw ValidationException::withMessages(['user_id' =>
                    __('The user id is required.')
                ]);
            }
        } else {
            if ($user = User::where('id', $this->request->user_id)->first()) {
                $address = $user->userTeams->reject(function ($team) use ($invoice_address) {
                    return !$team->address()->where('addresses.id', $invoice_address)->exists();
                })->map(function ($team) use ($invoice_address) {
                    return $team->address()->where('addresses.id', $invoice_address)->exists();
                })->first();
                if (Settings::useTeamAddress()?->value) {
                    if (!$address) {
                        throw ValidationException::withMessages(['address' =>
                            __('The selected invoice Address is not related to the existing team where the user in it.')
                        ]);
                    }
                } else {
                    if (!$user
                            ->addresses()
                            ->where('addresses.id', $invoice_address)
                            ->exists() && !$address) {
                        throw ValidationException::withMessages(['address' =>
                            __('The selected invoice Address is not related to the selected user id.')
                        ]);
                    }
                }

            } else {
                throw ValidationException::withMessages(['user_id' =>
                    __('The user id is required.')
                ]);
            }
        }


        if (!Settings::useTeamAddress()?->value) {
            $address = $user->addresses()->where('addresses.id', $invoice_address)->first();
            if (!$address) {
                $address = $user->userTeams->reject(function ($team) use ($invoice_address) {
                    return !$team->address()->where('addresses.id', $invoice_address)->first();
                })->map(function ($team) use ($invoice_address) {
                    return $team->address()->where('addresses.id', $invoice_address)->first();
                })->first();
            }
        } else {
            $address = $user->userTeams->reject(function ($team) use ($invoice_address) {
                return !$team->address()->where('addresses.id', $invoice_address)->first();
            })->map(function ($team) use ($invoice_address) {
                return $team->address()->where('addresses.id', $invoice_address)->first();
            })->first();
        }
        $this->invoice_address_type = "invoice";
        $this->invoice_address_company_name = $address->pivot->company_name;
        $this->invoice_address_full_name = $address->pivot->full_name;
        $this->invoice_address_tax_nr = $address->pivot->tax_nr;
        $this->invoice_address_phone_number = $address->pivot->phone_number;
        $this->invoice_team_address = $address->pivot->team_address;
        $this->invoice_team_id = $address->pivot->pivotParent->id;
        $this->invoice_team_name = $address->pivot->pivotParent->name;

        $this->invoice_address = $invoice_address;
        return $this->invoice_address;
    }

    /**
     * @return string|null
     */
    final public function address_type(): ?string
    {
        if ($this->request->get('address_type')) {
            $this->address_type = $this->request->get('address_type');
        }
        return $this->address_type;
    }

    /**
     * @return string|null
     */
    final public function address_company_name(): ?string
    {
        if ($this->request->get('address_company_name')) {
            $this->address_company_name = $this->request->get('address_company_name');
        }
        return $this->address_company_name;
    }

    /**
     * @return string|null
     */
    final public function address_full_name(): ?string
    {
        if ($this->request->get('address_full_name')) {
            $this->address_full_name = $this->request->get('address_full_name');
        }
        return $this->address_full_name;
    }

    /**
     * @return string|null
     */
    final public function address_tax_nr(): ?string
    {
        if ($this->request->get('address_tax_nr')) {
            $this->address_tax_nr = $this->request->get('address_tax_nr');
        }
        return $this->address_tax_nr;
    }

    /**
     * @return string|null
     */
    final public function address_phone_number(): ?string
    {
        if ($this->request->get('address_phone_number')) {
            $this->address_phone_number = $this->request->get('address_phone_number');
        }
        return $this->address_phone_number;
    }

    /**
     * @return bool|null
     */
    final public function address_team_address(): ?bool
    {
        $this->address_team_address = Settings::useTeamAddress()?->value;
        return $this->address_team_address;
    }

    /**
     * @return int|null
     */
    final public function address_team_id(): ?int
    {
        return  Settings::useTeamAddress()?->value? $this->team_id: null;
    }

    /**
     * @return string|null
     */
    final public function address_team_name(): ?string
    {
        return  Settings::useTeamAddress()?->value? $this->team_name: null;
    }



    /**
     * @return string|null
     */
    final public function invoice_address_type(): ?string
    {
        return $this->invoice_address_type = 'invoice';
    }

    /**
     * @return string|null
     */
    final public function invoice_address_company_name(): ?string
    {
        if ($this->request->get('invoice_address_company_name')) {
            $this->invoice_address_company_name = $this->request->get('invoice_address_company_name');
        }
        return $this->invoice_address_company_name;
    }

    /**
     * @return string|null
     */
    final public function invoice_address_full_name(): ?string
    {
        if ($this->request->get('invoice_address_full_name')) {
            $this->invoice_address_full_name = $this->request->get('invoice_address_full_name');
        }
        return $this->invoice_address_full_name;
    }

    /**
     * @return string|null
     */
    final public function invoice_address_tax_nr(): ?string
    {
        if ($this->request->get('invoice_address_tax_nr')) {
            $this->invoice_address_tax_nr = $this->request->get('invoice_address_tax_nr');
        }
        return $this->invoice_address_tax_nr;
    }

    /**
     * @return string|null
     */
    final public function invoice_address_phone_number(): ?string
    {
        if ($this->request->get('invoice_address_phone_number')) {
            $this->invoice_address_phone_number = $this->request->get('invoice_address_phone_number');
        }
        return $this->invoice_address_phone_number;
    }

    /**
     * @return bool|null
     */
    final public function invoice_team_address(): ?bool
    {
        $this->invoice_team_address = Settings::useTeamAddress()?->value;
        return $this->invoice_team_address;
    }

    /**
     * @return int|null
     */
    final public function invoice_team_id(): ?int
    {
        return  Settings::useTeamAddress()? $this->invoice_team_id: null;
    }

    /**
     * @return string|null
     */
    final public function invoice_team_name(): ?string
    {
        return  Settings::useTeamAddress()?->value? $this->invoice_team_name: null;
    }

    /**
     * Validates the order items.
     *
     * @throws ValidationException
     */
    private function validateOrderItems(): void
    {
        collect($this->order->items)->map(function ($item) {
            if ($this->order->delivery_multiple) {
                if ($item->delivery_separated && $item->children->count() === 0) {
                    throw ValidationException::withMessages(['addresses' =>
                        __("Item {$item->id} must have addresses")
                    ]);
                } elseif (!$item->delivery_separated && $item->addresses->count() === 0) {
                    throw ValidationException::withMessages(['address' =>
                        __("Item {$item->id} must have address")
                    ]);
                }
            }
        });
    }

    /**
     * @return bool
     */
    private function editing(): bool
    {
        return $this->request->get('editing');
    }

    /**
     * @return bool
     */
    private function locked(): bool
    {
        return $this->request->get('locked');
    }

    /**
     * @return array|null
     */
    final public function prepare(): ?array
    {

        $methods = get_class_methods($this);
        $ex = [
            '__construct',
            'prepare',
            'validateOrderItems'
        ];

        $methods = array_values(array_diff($methods, $ex));
        foreach ($methods as $method) {
//            $this->delivery_multiple();
            if (array_key_exists($method, $this->request->all())) {
                $this->response[$method] = $this->{$method}();
            } else {
                $this->response[$method] = $this->{$method};
            }

        }

        return array_merge($this->response);
    }
}
