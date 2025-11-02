<?php

namespace Modules\Cms\Entities\Eloquent;
use Illuminate\Database\Eloquent\Collection as Base;

class Collection extends Base
{

    /**
     * @param string $childrenRelation
     * @return Collection
     * override the collection class to handle multilingual row_id
     */
    public function toTree(string $childrenRelation = 'children'): Collection
    {
        if ($this->isEmpty()) {
            return $this;
        }

        $parentKeyName = $this->first()->getParentKeyName();
        $localKeyName = $this->first()->getLocalKeyName();
        $depthName = $this->first()->getDepthName();

        $depths = $this->pluck($depthName);

        $tree = new static(
            $this->where($depthName, $depths->min())->values()
        );

        $itemsByParentKey = $this->groupBy($parentKeyName);

        foreach($this->items as $item) {
            $itemsByParentKeyUnique = optional(optional($itemsByParentKey)[$item->$localKeyName])->unique();
            $item->setRelation($childrenRelation, $itemsByParentKeyUnique ?? new static());
        }

        return $tree;
    }

}
