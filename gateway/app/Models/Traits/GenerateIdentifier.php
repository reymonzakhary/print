<?php


namespace App\Models\Traits;


use App\Generates\Identifier;

trait GenerateIdentifier
{

    /**
     * @return string
     */
    public function getIdentifierAttribute(): string
    {
        return $this->generateIdentifier($this->id, __CLASS__);
    }

    /**
     * @param int    $id
     * @param string $model
     * @return string
     */
    final public function generateIdentifier(
        int    $id,
        string $model
    ): string
    {
        return app(Identifier::class)->generate($id, $model);
    }

}
