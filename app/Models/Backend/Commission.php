<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function account(){
        return $this->belongsTo(Account::class);
    }
}
