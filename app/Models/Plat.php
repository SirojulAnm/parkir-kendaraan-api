<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plat extends Model
{
    use HasFactory;

    protected $table = 'plat';

    protected $fillable = [
        'name',
        'check_in',
        'check_out',
        'unique_code',
        'cost'
    ];
}
