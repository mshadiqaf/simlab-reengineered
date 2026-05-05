<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPengajuanRuangan extends Model
{
    protected $table = 'room_submission_details';
    protected $guarded = [];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Pengajuan::class, 'submission_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Ruangan::class, 'room_id');
    }
}
