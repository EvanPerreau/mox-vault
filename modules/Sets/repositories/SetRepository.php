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
    public function updateOrCreate(SetDto $setDto): Set
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
     * Create or update an entity from an array of data.
     *
     * @param array<string, mixed> $data
     * @return Set
     */
    public function updateOrCreateFromArray(array $data): Set
    {
        $setDto = SetDto::fromArray($data);

        return Set::updateOrCreate(
            ['id' => $setDto->uuid], // Critères de recherche
            [
                'code' => $setDto->code,
                'name' => $setDto->name,
                'uri' => $setDto->uri,
                'released_at' => $setDto->released_at,
                'set_type' => $setDto->set_type,
                'card_count' => $setDto->card_count,
                'parent_set_code' => $setDto->parent_set_code,
                'digital' => $setDto->digital,
                'nonfoil_only' => $setDto->nonfoil_only,
                'foil_only' => $setDto->foil_only,
                'icon_svg_uri' => $setDto->icon_svg_uri
            ]
        );
    }
}
