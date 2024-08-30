<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    
    use HasFactory, SoftDeletes;
    use SoftDeletes;

    protected $fillable = ['libelle', 'description', 'price', 'stock'];

    // Masquer ces attributs dans toutes les réponses JSON
    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];
    
}