<?php

namespace Modules\Sets\entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Modules\CommonBusinessException\exceptions\BadParameterException;

/**
 * Set Eloquent model.
 *
 * @package Modules\Sets\entities
 */
class Set extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'code',
        'name',
        'uri',
        'released_at',
        'set_type',
        'card_count',
        'parent_set_code',
        'digital',
        'nonfoil_only',
        'foil_only',
        'icon_svg_uri'
    ];

    /**
     * UUID of the set.
     *
     * @throws BadParameterException When UUID is empty
     */
    public string $uuid {
        get => $this->attributes['id'];
        set(string $uuid) {
            if (Str::length($uuid) === 0 || Str::isBlank($uuid)) {
                throw new BadParameterException('uuid can\'t be empty');
            }
            $this->attributes['id'] = $uuid;
        }
    }

    /**
     * Code of the set
     *
     * @throws BadParameterException When the code is empty
     */
    public string $code {
        get => $this->attributes['code'];
        set(string $code) {
            if (Str::length($code) === 0 || Str::isBlank($code)) {
                throw new BadParameterException('code can\'t be empty');
            }
            $this->attributes['code'] = $code;
        }
    }

    /**
     * Name of the set.
     *
     * @throws BadParameterException When name is empty
     */
    public string $name {
        get => $this->attributes['name'];
        set(string $name) {
            if (Str::length($name) === 0 || Str::isBlank($name)) {
                throw new BadParameterException('name can\'t be empty');
            }
            $this->attributes['name'] = $name;
        }
    }

    /**
     * URI of the set.
     *
     * @throws BadParameterException When URI is empty
     */
    public string $uri {
        get => $this->attributes['uri'];
        set(string $uri) {
            if (Str::length($uri) === 0 || Str::isBlank($uri)) {
                throw new BadParameterException('uri can\'t be empty');
            }
            $this->attributes['uri'] = $uri;
        }
    }

    /**
     * Release date of the set.
     *
     * @throws BadParameterException When release date is empty
     */
    public ?string $released_at {
        get => $this->attributes['released_at'] ?? null;
        set(?string $releasedAt) {
            $this->attributes['released_at'] = $releasedAt;
        }
    }

    /**
     * Type of the set.
     *
     * @throws BadParameterException When set type is empty
     */
    public string $set_type {
        get => $this->attributes['set_type'];
        set(string $setType) {
            if (Str::length($setType) === 0 || Str::isBlank($setType)) {
                throw new BadParameterException('set_type can\'t be empty');
            }
            $this->attributes['set_type'] = $setType;
        }
    }

    /**
     * Number of cards in the set.
     *
     * @throws BadParameterException When card count is negative
     */
    public int $card_count {
        get => (int)$this->attributes['card_count'];
        set(int $cardCount) {
            if ($cardCount < 0) {
                throw new BadParameterException('card_count can\'t be negative');
            }
            $this->attributes['card_count'] = $cardCount;
        }
    }

    /**
     * Parent set code of the set.
     */
    public ?string $parent_set_code {
        get => $this->attributes['parent_set_code'] ?? null;
        set(?string $parentSetCode) {
            $this->attributes['parent_set_code'] = $parentSetCode;
        }
    }

    /**
     * Whether the set is digital.
     */
    public bool $digital {
        get => (bool)$this->attributes['digital'];
        set(bool $digital) {
            $this->attributes['digital'] = $digital;
        }
    }

    /**
     * Whether the set is nonfoil only.
     */
    public bool $nonfoil_only {
        get => (bool)$this->attributes['nonfoil_only'];
        set(bool $nonfoilOnly) {
            $this->attributes['nonfoil_only'] = $nonfoilOnly;
        }
    }

    /**
     * Whether the set is foil only.
     */
    public bool $foil_only {
        get => (bool)$this->attributes['foil_only'];
        set(bool $foilOnly) {
            $this->attributes['foil_only'] = $foilOnly;
        }
    }

    /**
     * Icon SVG URI of the set.
     */
    public ?string $icon_svg_uri {
        get => $this->attributes['icon_svg_uri'] ?? null;
        set(?string $iconSvgUri) {
            $this->attributes['icon_svg_uri'] = $iconSvgUri;
        }
    }

    /**
     * Register a new set
     *
     * @throws BadParameterException When one or more parameter isn't correct
     */
    public function register(
        string $uuid,
        string $code,
        string $name,
        string $uri,
        string $released_at,
        string $set_type,
        int $card_count,
        string $parent_set_code,
        boolean $digital,
        boolean $nonfoil_only,
        boolean $foil_only,
        string $icon_svg_uri
    ): void {
        $this->uuid = $uuid;
        $this->code = $code;
        $this->name = $name;
        $this->uri = $uri;
        $this->released_at = $released_at;
        $this->set_type = $set_type;
        $this->card_count = $card_count;
        $this->parent_set_code = $parent_set_code;
        $this->digital = $digital;
        $this->nonfoil_only = $nonfoil_only;
        $this->foil_only = $foil_only;
        $this->icon_svg_uri = $icon_svg_uri;
    }
}
