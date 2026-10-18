<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ComparisonRow extends Model
{
    protected $fillable = ['page_key', 'sort_order', 'label'];

    public function scopeOnPage(Builder $query, string $page): Builder
    {
        return $query->where('page_key', $page)->orderBy('sort_order')->orderBy('id');
    }
}
