<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collector extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function sectors(){
        return $this->belongsToMany(Sector::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function operations(){
        return $this->hasMany(Operation::class);
    }
}
