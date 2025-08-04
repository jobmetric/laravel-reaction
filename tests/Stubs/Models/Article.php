<?php

namespace JobMetric\Reaction\Tests\Stubs\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JobMetric\Reaction\Tests\Stubs\Factories\ArticleFactory;
use JobMetric\Reaction\HasReaction;

/**
 * @property int $id
 * @property string $title
 * @property string $status
 *
 * @method static create(string[] $array)
 */
class Article extends Model
{
    use HasFactory, HasReaction;

    public $timestamps = false;
    protected $fillable = [
        'title',
        'status'
    ];
    protected $casts = [
        'title' => 'string',
        'status' => 'string',
    ];

    protected static function newFactory(): ArticleFactory
    {
        return ArticleFactory::new();
    }
}
