<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sector extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function collectors(){
        return $this->belongsToMany(Collector::class);
    }

    public function clients(){
        return $this->hasMany(Client::class);
    }
}
