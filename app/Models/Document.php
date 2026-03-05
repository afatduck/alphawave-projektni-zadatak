<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected function documentType(): Attribute
    {
        return Attribute::make(
            get: fn () => strtoupper(pathinfo($this->file_name, PATHINFO_EXTENSION))
        );
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->title)) {
                $model->title = $model->file_name; 
            }
        });
    }

    protected $fillable = ['product_id', 'title', 'file_path', 'file_name', 'mime_type', 'tags'];

    protected $casts = ['tags' => 'array'];
}
