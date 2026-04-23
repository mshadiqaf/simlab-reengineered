<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisPengujian extends Model
{
    protected $guarded = [];

    public function detailPengajuan()
    {
        return $this->hasMany(DetailPengajuanUji::class, 'jenis_pengujian_id');
    }
}
