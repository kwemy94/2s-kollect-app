<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function accounts(){
        return $this->hasMany(Account::class);
    }

    public function collector(){
        return $this->belongsTo(Collector::class);
    }
}
