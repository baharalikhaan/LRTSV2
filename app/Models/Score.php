<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'label',
        'category',
        'value',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get score value by name
     */
    public static function getValueByName(string $name): float
    {
        $score = static::where('name', $name)->where('is_active', true)->first();
        return $score ? (float) $score->value : 0;
    }

    /**
     * Get all scores by category
     */
    public static function getByCategory(string $category)
    {
        return static::where('category', $category)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get all active scores as name => value array
     */
    public static function getMap(): array
    {
        return static::where('is_active', true)
            ->pluck('value', 'name')
            ->toArray();
    }
}
