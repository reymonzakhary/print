<?php

namespace App\Validators;

use App\Enums\Status;
use App\Facades\Settings;
use App\Http\Requests\Order\QuotationUpdateRequest;
use App\Models\Tenants\Address;
use App\Models\Tenants\Order;
use App\Models\Tenants\Quotation;
use App\Models\Tenants\User;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

final class PrepareQuotationValidator
{
    protected ?string $reference = NULL;
    protected ?int $order_nr = NULL;
    protected ?bool $type;
    protected ?int $st = 300;
    protected ?string $st_message = null;
    protected ?string $message = null;
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
    protected Quotation $quotation;
    protected QuotationUpdateRequest $request;
    protected array $response;
    protected ?int $expire_at;
    protected ?string $connection;
    protected ?bool $archived;
    protected ?bool $editing;
    protected ?bool $locked;

    /**
     * PrepareOrderValidator constructor.
     * @param QuotationUpdateRequest $request
     * @param Quotation $quotation
     */
    public function __construct(
        QuotationUpdateRequest $request,
        Quotation              $quotation
    )
    {
        $this->quotation = $quotation;
        $this->updated_by = $request->user()->id; // protected
        $this->request = $request;
        /** @var Quotation $this reference */
        $this->reference = $this->quotation->reference;
        $this->connection = $this->quotation->connection;
        $this->order_nr = $this->quotation->order_nr;
        $this->type = $this->quotation->type;
        $this->st = $this->quotation->st;
        $this->st_message = $this->quotation->st_message;
        $this->delivery_multiple = $this->quotation->delivery_multiple;
        $this->delivery_pickup = $this->quotation->delivery_pickup;

        /** @var  Address $address */
        $address = $this->quotation->delivery_address()->first();
        $this->address = $address?->id;
        $this->address_type = $address?->pivot?->type;
        $this->address_full_name = $address?->pivot?->full_name;
        $this->address_company_name = $address?->pivot?->company_name;
        $this->address_tax_nr = $address?->pivot?->tax_nr;
        $this->address_phone_number = $address?->pivot?->phone_number;
        $this->address_team_address = $address?->pivot?->team_address;
        $this->address_team_id = $address?->pivot?->team_id;
        $this->address_team_name = $address?->pivot?->team_name;

        /** @var  Address $invoice_address */
        $invoice_address = $this->quotation->invoice_address()->first();
        $this->invoice_address = $invoice_address?->id;
        $this->invoice_address_type = $invoice_address?->pivot->type;
        $this->invoice_address_full_name = $invoice_address?->pivot->full_name;
        $this->invoice_address_company_name = $invoice_address?->pivot->company_name;
        $this->invoice_address_tax_nr = $invoice_address?->pivot->tax_nr;
        $this->invoice_address_phone_number = $invoice_address?->pivot->phone_number;
        $this->invoice_team_address = $invoice_address?->pivot->team_address;
        $this->invoice_team_id = $invoice_address?->pivot->team_id;
        $this->invoice_team_name = $invoice_address?->pivot->team_name;


        $this->shipping_cost = $this->quotation->shipping_cost;
        $this->note = $this->quotation->note;
        $this->message = $this->quotation->message;
        $this->ctx_id = $this->quotation->ctx_id;
        $this->user_id = $this->quotation->user_id;
        $this->discount_id = $this->quotation->discount_id;
        $this->price = $this->quotation->price->amount();
        $this->expire_at = optional($this->quotation->expire_at)->timestamp;

        $this->archived = $this->quotation->archived;
        $this->editing = $this->quotation->getAttribute('editing');
        $this->locked = $this->quotation->getAttribute('locked');
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
     * @return bool
     * @throws ValidationException
     */
    final public function type(): bool
    {
        if ($this->st === Status::EDITING->value || $this->st === Status::LOCKED->value) {
            throw ValidationException::withMessages([
                'type' => [
                    __("Quotation on editing mode or locked, please finishing editing first and try again later.")
                ],
            ]);
        }

        $this->type = $this->request->get('type');
        if ($this->type) {
            $this->st(Status::NEW->value);
            $order = Order::where('id', $this->quotation->id)->first();

            if($address = $this->quotation->delivery_address()->first()) {
                $order->delivery_address()->sync([$address->id => [
                    'type' => $address->pivot->type,
                    'full_name' => $address->pivot->full_name,
                    'company_name' => $address->pivot->company_name,
                    'phone_number' => $address->pivot->phone_number,
                    'tax_nr' => $address->pivot->tax_nr,
                    'team_address' => $address->pivot->team_address,
                    'team_id' => $address->pivot->team_id,
                    'team_name' => $address->pivot->team_name
                ]]);
                $this->quotation->delivery_address()->detach();
                $this->address = null;
            }

            if($invoice = $this->quotation->invoice_address()->first()) {
                $order->invoice_address()->sync([$invoice->id => [
                    'type' => $invoice->pivot->type,
                    'full_name' => $invoice->pivot->full_name,
                    'company_name' => $invoice->pivot->company_name,
                    'phone_number' => $invoice->pivot->phone_number,
                    'tax_nr' => $invoice->pivot->tax_nr,
                    'team_address' => $invoice->pivot->team_address,
                    'team_id' => $invoice->pivot->team_id,
                    'team_name' => $invoice->pivot->team_name
                ]]);
                $this->invoice_address = null;
                $this->quotation->invoice_address()->detach();
            }

        }

        return $this->type;
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
    final public function expire_at(): ?int
    {
        $this->expire_at = Carbon::now()->addDays($this->request->get('expire_at'))->timestamp;
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
     * Update the status of the item
     *
     * @param int|null $status The new status value
     * @return int|null The updated status value
     * @throws ValidationException
     */
    final public function st(
        null|int $status = null
    ): ?int
    {
        if ($this->request->get('st') === Status::EDITING->value) {
            $this->st = $this->request->get('st');
            $this->quotation->items()->update([
                'st' => Status::EDITING->value
            ]);
        }

        if(
            ($this->request->get('st') === Status::NEW->value
            && $this->quotation->st !== Status::REJECTED->value ) &&
            (
                $this->quotation->connection !== 'cec' &&
                $this->quotation->created_from !== 'api'&&
                optional($this->quotation)->orderedBy === null
            )
        ) {
            throw ValidationException::withMessages([
                'customer' => [
                    __('Oops! You haven’t selected a customer yet. Please add one or choose from the list.')
                ]
            ]);
        }


        if (
            $status ||
            ($this->st === Status::DRAFT->value | Status::EDITING->value && $this->request->get('st') === Status::NEW->value)
        ) {
            $this->validateOrderItems();
            $this->st = $status ?? $this->request->get('st');
        } elseif ($this->request->get('st') === Status::DECLINED->value) {
            $this->st = $this->request->get('st');
            $this->quotation->items()->update([
                'st' => Status::DECLINED->value
            ]);
        }else{
            $this->st = $this->request->get('st');
        }

        return $this->st;
    }

    /**
     *
     * @return boolean
     */
    final public function archived(): bool
    {
        return false;
    }

    /**
     * @return string|null
     * @throws ValidationException
     */
    final public function st_message(): ?string
    {
        if ($this->st === Status::REJECTED && !$this->request->get('st_message')) {
            throw ValidationException::withMessages(['st_message' =>
                __("Status message is required.")
            ]);
        }

        return $this->st_message = $this->request->get('st_message');
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
     */
    final public function ctx_id(): ?int
    {
        // check if user in request
        if (is_null($this->request->user_id) && optional($this->quotation)->orderedBy !== null) {
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
            if ($this->request->ctx_id !== NULL) {
                if (!$user->contexts()->where('contexts.id', $this->request->ctx_id)->exists()) {
                    throw ValidationException::withMessages(['user_id' =>
                        __('The selected user id is not belong to selected context.')
                    ]);
                }
            } else {
                if (!$user->contexts()->where('contexts.id', $this->ctx_id)->exists()) {
                    $this->ctx_id = $user->contexts()->first()?->id;
                    if ($this->ctx_id !== NULL) {
                        $this->quotation->update(['ctx_id' => $user->contexts()->first()->id]);
                    } else {
                        throw ValidationException::withMessages(['user_id' =>
                            __('The selected user id is not belong to existing context.')
                        ]);
                    }
                }
            }
        } elseif ($this->connection) {
            return $this->user_id;
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
        } else {
            $this->quotation->items()->each(function ($item) {
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
     * @return string|null
     */
    final public function message(): ?string
    {
        if ($this->request->get('message')) {
            return $this->message = $this->request->get('message');
        }

        return $this->message;
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
            if (!$address = $this->quotation
                ->context
                ?->addresses()
                ->where('addresses.id', $this->request->get('address'))
                ->first()
            ) {
                throw ValidationException::withMessages([
                    'address' =>
                        __('The selected Address is not related to the quotation context.')
                ]);
            }

            $this->address_type = 'pickup';

            $this->address_team_address = false;
            $this->address_team_id = null;
            $this->address_team_name = null;
        } else {
            if (is_null($this->request->user_id)) {
                if ($user = $this->quotation->orderedBy) {
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
            if ($user = $this->quotation->orderedBy) {
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
        return Settings::useTeamAddress()?->value ? $this->team_id : null;
    }

    /**
     * @return string|null
     */
    final public function address_team_name(): ?string
    {
        return Settings::useTeamAddress()?->value ? $this->team_name : null;
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
        return Settings::useTeamAddress()?->value ? $this->invoice_team_id : null;
    }

    /**
     * @return string|null
     */
    final public function invoice_team_name(): ?string
    {
        return Settings::useTeamAddress()?->value ? $this->invoice_team_name : null;
    }

    /**
     * @return bool
     * @throws ValidationException
     */
    private function editing(): bool
    {
        if(
            !$this->request->get('editing') &&
            optional($this->quotation)->orderedBy === null &&
            optional($this->quotation)->connection !== 'cec' &&
            optional($this->quotation)->created_from !== 'api' &&
            $this->quotation->st !== Status::REJECTED->value
        ) {
            throw ValidationException::withMessages([
                'customer' => [
                    __('Oops! You haven’t selected a customer yet. Please add one or choose from the list.')
                ]
            ]);
        }
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
     * Validates the order items.
     *
     * @throws ValidationException
     */
    private function validateOrderItems(): void
    {
        collect($this->quotation->items)->map(function ($item) {
            if ($this->quotation->delivery_multiple) {
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
