<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPengajuanUji extends Model
{
    protected $table = 'test_submission_details';
    protected $guarded = [];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Pengajuan::class, 'submission_id');
    }

    public function testType(): BelongsTo
    {
        return $this->belongsTo(JenisPengujian::class, 'test_type_id');
    }
}
