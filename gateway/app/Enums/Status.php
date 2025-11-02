<?php

namespace App\Enums;


use Carbon\Carbon;
use EmreYarligan\EnumConcern\EnumConcern;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use App\Models\Tenants\Status as StatusModel;
use LogicException;

enum Status: int
{
    use EnumConcern;

    case DRAFT = 300;
    case PENDING = 301;
    case NEW = 302;
    case IN_PROGRESS = 303;
    case BEING_SHIPPED = 304;
    case CANCELED = 305;
    case READY = 306;
    case DELIVERED = 307;
    case DONE = 308;
    case LOCKED = 309;
    case ARCHIVED = 310;
    case BLOCKED = 311;
    case MAILING = 312;
    case MAILED = 313;
    case PROCESSING = 314;
    case EXPIRING = 315;
    case EXPIRED = 316;
    case EDITABLE = 317;
    case IN_PRODUCTION = 318;
    case REJECTED = 319;
    case ACCEPTED = 320;
    case FAILED = 321;
    case WAITING_FOR_RESPONSE = 322;
    case DECLINED = 323;
    case EDITING = 324;
    case UN_PAID = 325;
    case PAID = 326;
    case SUSPENDED = 327;

    /**
     * Get the name of a status based on its value.
     *
     * @param int $value The value of the status.
     * @return array|null The name of the status, or null if not found.
     */
    public static function getStatusByCode(StatusModel|int $value)
    {

        foreach (self::getAllAsModel() as $status) {
            if ($status->code === $value) {
                return $status;
            }
        }
        return null;
    }

    /**
     * Get all statuses as an array.
     *
     * @return array
     */
    public static function getAllStatuses(): array
    {
        return array_map(fn($case) => [$case->name => $case->value], self::cases());
    }

    /**
     * Get all cases as a collection of Status models.
     *
     * @return Collection
     */
    public static function getAllAsModel(): Collection
    {
        return collect(array_map(fn($case) => new \App\Models\Tenants\Status([
            'id'    => rand(100000, 999999),
            'name'  => Str::lower($case->name),
            'code'  => $case->value,
            'description' => '',
            'created_at' => Carbon::now(),
            'updated_at' =>  Carbon::now(),
        ]), self::cases()));
    }

    /**
     * Return the sequential statuses of the order item
     *
     * @return int
     */
    public function getSequenceForOrderItem(): int
    {
        return match ($this) {
            self::DRAFT => 1,
            self::NEW, self::PROCESSING => 2,
            self::IN_PROGRESS => 3,
            self::IN_PRODUCTION => 4,
            self::READY => 5,
            self::BEING_SHIPPED => 6,
            self::DELIVERED => 7,

            default => throw new LogicException(
                'Current status does not have any sequence available'
            )
        };
    }
}
