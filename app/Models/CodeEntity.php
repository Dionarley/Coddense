<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CodeEntity extends Model
{
    protected $fillable = [
        'repository_id',
        'type',
        'name',
        'namespace',
        'file_path',
        'language',
        'details',
        'vulnerabilities',
        'technical_debt',
    ];

    protected $casts = [
        'details' => 'array',
        'vulnerabilities' => 'array',
        'technical_debt' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function repository(): BelongsTo
    {
        return $this->belongsTo(Repository::class);
    }
}
