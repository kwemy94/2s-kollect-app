<?php

namespace App\Models\Brivael;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Etablissement extends Model
{
    use HasFactory;
    // protected $protected = ['ets_name', 'ets_email', 'settings'];
    protected $guarded = ['id'];

    public function users() {
        return $this->hasMany(User::class);
    }
}
