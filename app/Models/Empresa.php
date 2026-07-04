<?php

namespace App\Models;

use App\Models\Concerns\TracksUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory, TracksUser;

    protected $fillable = [
        'nome',
        'cnpj',
        'status',
    ];

    public function usuarios()
    {
        return $this->hasMany(User::class);
    }
}
