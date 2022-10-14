<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    public function commissions(){
        return $this->hasMany(Commission::class);
    }

    public function client(){
        return $this->belongsTo(Client::class);
    }

    public function operations(){
        return $this->hasMany(Operation::class);
    }


}
