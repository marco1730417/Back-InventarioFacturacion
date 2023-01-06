<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Retencion extends Model
{
    //
    protected $fillable = [
        'fecha', 'retencioniva',
        'impuestorenta', 'venId','comId'
        
    ];
}