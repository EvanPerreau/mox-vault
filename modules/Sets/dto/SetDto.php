<?php

namespace Modules\Sets\dto;

/**
 * Data Transfer Object for Set entity.
 * 
 * @package Modules\Sets\dto
 */
class SetDto
{
    /**
     * @param string $uuid
     * @param string $code
     * @param string $name
     * @param string $uri
     * @param string $released_at
     * @param string $set_type
     * @param int $card_count
     * @param string|null $parent_set_code
     * @param bool $digital
     * @param bool $nonfoil_only
     * @param bool $foil_only
     * @param string|null $icon_svg_uri
     */
    public function __construct(
        public readonly string $uuid,
        public readonly string $code,
        public readonly string $name,
        public readonly string $uri,
        public readonly string $released_at,
        public readonly string $set_type,
        public readonly int $card_count,
        public readonly ?string $parent_set_code = null,
        public readonly bool $digital = false,
        public readonly bool $nonfoil_only = false,
        public readonly bool $foil_only = false,
        public readonly ?string $icon_svg_uri = null
    ) {
    }

    /**
     * Create a SetDto from an array of data.
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? '',
            code: $data['code'] ?? '',
            name: $data['name'] ?? '',
            uri: $data['uri'] ?? '',
            released_at: $data['released_at'] ?? '',
            set_type: $data['set_type'] ?? '',
            card_count: (int)($data['card_count'] ?? 0),
            parent_set_code: $data['parent_set_code'] ?? null,
            digital: (bool)($data['digital'] ?? false),
            nonfoil_only: (bool)($data['nonfoil_only'] ?? false),
            foil_only: (bool)($data['foil_only'] ?? false),
            icon_svg_uri: $data['icon_svg_uri'] ?? null
        );
    }
}
