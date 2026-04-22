<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPengajuanUji extends Model
{
    protected $guarded = [];

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(Pengajuan::class);
    }

    public function jenisPengujian(): BelongsTo
    {
        return $this->belongsTo(JenisPengujian::class);
    }
}
