<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alat extends Model
{
    protected $table = 'equipment';
    protected $guarded = [];

    public function detailPengajuan()
    {
        return $this->hasMany(DetailPengajuanAlat::class, 'alat_id');
    }
}
