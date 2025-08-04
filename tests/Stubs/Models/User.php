<?php

namespace JobMetric\Reaction\Tests\Stubs\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JobMetric\Reaction\CanReact;
use JobMetric\Reaction\Tests\Stubs\Factories\UserFactory;

/**
 * @property int $id
 * @property string $name
 *
 * @method static create(string[] $array)
 */
class User extends Model
{
    use HasFactory, CanReact;

    public $timestamps = false;
    protected $fillable = [
        'name'
    ];
    protected $casts = [
        'name' => 'string'
    ];

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
