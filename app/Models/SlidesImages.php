<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlidesImages extends Model
{
    use HasFactory;
      // Define the table associated with the model
    protected $table = 'slides_images'; 

    // Specify the fillable fields
    protected $fillable = [
        'path',
        'title',
        'link',
        'role',
    ];

}
