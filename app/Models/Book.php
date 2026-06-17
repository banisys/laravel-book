<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'pdf_path'])]
class Book extends Model
{

    protected $fillable = [
        'title',
        'pdf_path',
    ];

    public function pages()
    {
        return $this->hasMany(Page::class);
    }
}
