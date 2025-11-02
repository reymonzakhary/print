<?php

namespace App\Http\Resources\Quotations;

use App\Http\Resources\Users\UserHistoryResource;
use App\Models\User;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/**
 * Class QuotationActivityResource
 * @package App\Http\Resources\QuotationActivityResource
 * @OA\Schema(
 * )
 */
class QuotationActivityResource extends JsonResource
{
    /**
     * @var array
     */
    protected array $withoutFields = [];

    /**
     * @var array
     */
    protected array $withoutChildrenFields = [];

    /**
     * @param mixed $resource
     * @return AnonymousResourceCollection|mixed
     */
    public static function collection($resource)
    {
        return tap(new QuotationActivityResourceCollection($resource), function ($collection) {
            $collection->collects = __CLASS__;
        });
    }

    /**
     * @OA\Property(format="int64", title="ID", default="1158", description="ID", property="id"),
     * @OA\Property(format="string", title="user", default="has update expire_at", description="event", property="user"),
     * @OA\Property(format="string", title="event", default="eg", description="event", property="event"),
     * @OA\Property(format="string", title="from", default="egy", description="from", property="from"),
     * @OA\Property(format="int64", title="to", default="818", description="to", property="to"),
     * @OA\Property(format="string", title="created_at", default="022-04-04T09:22:31.000000Z", description="created_at", property="created_at"),
     * @OA\Property(format="date", title="hour", default="11:22:31", description="created date & time", property="hour"),
     * @OA\Property(format="date", title="day", default="Mon 4", description="last update date & time", property="day"),
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        $to = $this->to;
        if (str_contains($this->event, 'has add new item with id') || str_contains($this->event, 'has change product on item')) {
            $to = optional($this->order->items->where('id', (int)$this->to)->first())->product;
        }
        if($this->external) {
            $user = User::with('profile')->where('id', $this->created_by)->first();
        }else{
            $user = $this->user;
        }
        return $this->filterFields([
            'id' => $this->id,
            'user' => UserHistoryResource::make($user),
            'event' => $this->event,
            'from' => $this->from,
            'to' => $to,
            'created_at' => $this->created_at,
            'hour' => $this->created_at->format('H:i:s'),
            'day' => $this->created_at->format('D j'),
            'external' => $this->external
        ]);
    }

    /**
     * Set the keys that are supposed to be filtered out from children tables.
     * @param array $fields
     * @return $this
     */
    final public function hideChildren(array $fields): self
    {
        $this->withoutChildrenFields = $fields;
        return $this;
    }

    /**
     * Set the keys that are supposed to be filtered out.
     *
     * @param array $fields
     * @return $this
     */
    final public function hide(array $fields): self
    {
        $this->withoutFields = $fields;

        return $this;
    }

    /**
     * Remove the filtered keys.
     *
     * @param $array
     * @return array
     */
    protected function filterFields(array $array)
    {
        return collect($array)->forget($this->withoutFields)->toArray();
    }
}
