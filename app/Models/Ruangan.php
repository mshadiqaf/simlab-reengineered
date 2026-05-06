<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ruangan extends Model
{
    protected $table = 'rooms';
    protected $guarded = [];

    public function detailPengajuan()
    {
        return $this->hasMany(DetailPengajuanRuangan::class, 'ruangan_id');
    }
}
