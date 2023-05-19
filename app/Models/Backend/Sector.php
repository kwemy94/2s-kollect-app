<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sector extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    // public function collectors(){
    //     return $this->belongsToMany(Collector::class);
    // }

    public function clients(){
        return $this->hasMany(Client::class);
    }
}
