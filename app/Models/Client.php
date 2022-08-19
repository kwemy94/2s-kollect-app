<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function accounts(){
        return $this->hasMany(Account::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function sector(){
        return $this->belongsTo(Sector::class);
    }
}
