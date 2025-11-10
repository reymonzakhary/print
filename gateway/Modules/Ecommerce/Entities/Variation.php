<?php

namespace Modules\Ecommerce\Entities;

use App\Models\Traits\HasChildren;
use App\Models\Traits\Slugable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class Variation extends Model implements Sortable
{
    use Slugable, SortableTrait, HasChildren,
        HasRecursiveRelationships, HasFactory;

    protected $fillable = [];

    /**
     * sorting tables column
     * @var array
     */
    public $sortable = [
        'order_column_name' => 'sort',
        'sort_when_creating' => true,
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function stockCount()
    {
        return 2;
//        return $this->descendantsAndSelf()->sum(fn ($variation) => $variation->stockes->sum('amount'));
//        return $this->ancestorsAndSelf()->ordered();
    }
}
