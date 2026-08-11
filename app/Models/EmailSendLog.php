<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailSendLog extends Model
{
    protected $table = 'email_send_log';

    protected $fillable = [
        'sent_by',
        'recipient_email',
        'recipient_name',
        'cc',
        'subject',
        'body',
        'attachment_path',
        'attachment_original_name',
        'status',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
