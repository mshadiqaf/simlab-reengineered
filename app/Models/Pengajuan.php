<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pengajuan extends Model
{
    protected $table = 'submissions';
    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function detailRuangan(): HasOne
    {
        return $this->hasOne(DetailPengajuanRuangan::class, 'submission_id');
    }

    public function detailAlat(): HasOne
    {
        return $this->hasOne(DetailPengajuanAlat::class, 'submission_id');
    }

    public function detailUji(): HasOne
    {
        return $this->hasOne(DetailPengajuanUji::class, 'submission_id');
    }
}
