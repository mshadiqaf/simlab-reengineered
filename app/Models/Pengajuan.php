<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pengajuan extends Model
{
    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function detailRuangan(): HasOne
    {
        return $this->hasOne(DetailPengajuanRuangan::class);
    }

    public function detailAlat(): HasOne
    {
        return $this->hasOne(DetailPengajuanAlat::class);
    }

    public function detailUji(): HasOne
    {
        return $this->hasOne(DetailPengajuanUji::class);
    }
}
