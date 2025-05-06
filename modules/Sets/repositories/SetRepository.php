<?php

namespace Modules\Sets\repositories;

use Modules\Sets\dto\SetDto;
use Modules\Sets\entities\Set;

/**
 * Repository for Set entity.
 *
 * @package Modules\Sets\repositories
 */
class SetRepository
{
    /**
     * Find an entity by its ID.
     *
     * @param int $id
     * @return Set|null
     */
    public function findById(int $id): ?Set
    {
        return Set::find($id);
    }

    /**
     * Get all entities.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Set>
     */
    public function getAll()
    {
        return Set::all();
    }

    /**
     * Create a new entity.
     *
     * @param SetDto $setDto Data transfer object containing set data
     * @return Set
     */
    public function create(SetDto $setDto): Set
    {
        $set = new Set();
        $set->register(
            $setDto->uuid,
            $setDto->code,
            $setDto->name,
            $setDto->uri,
            $setDto->released_at,
            $setDto->set_type,
            $setDto->card_count,
            $setDto->parent_set_code,
            $setDto->digital,
            $setDto->nonfoil_only,
            $setDto->foil_only,
            $setDto->icon_svg_uri
        );
        $set->save();
        return $set;
    }

    /**
     * Create a new entity from an array of data.
     *
     * @param array<string, mixed> $data
     * @return Set
     */
    public function createFromArray(array $data): Set
    {
        return $this->create(SetDto::fromArray($data));
    }
}
