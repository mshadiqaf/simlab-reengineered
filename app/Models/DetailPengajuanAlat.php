<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPengajuanAlat extends Model
{
    protected $table = 'equipment_submission_details';
    protected $guarded = [];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Pengajuan::class, 'submission_id');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Alat::class, 'equipment_id');
    }
}
