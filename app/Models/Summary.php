<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Summary extends Model
{
    protected $fillable = [
        'book_id',
        'from_page',
        'to_page',
        'content',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
