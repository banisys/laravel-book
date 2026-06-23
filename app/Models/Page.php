<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'book_id',
        'page_number',
        'image_path',
        'content',
        'is_synced_to_rag'
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
