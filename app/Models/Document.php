<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'user_id', 'type', 'path', 'original_name', 'mime_type', 'size',
        'supervisor_id', 'status', 'comments', 'signed_at', 'signed_path', 'title'
    ];

    protected function casts(): array
    {
        return [
            'signed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Reports can only be previewed inline and signed (FPDI) when they are PDFs.
     * Legacy Word uploads are download-only.
     */
    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf'
            || \Illuminate\Support\Str::endsWith(strtolower((string) $this->original_name), '.pdf');
    }
}
