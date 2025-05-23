<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    //
    protected $table = 'webhook_logs';

    protected $fillable = [
        'webhook_id',
        'request_url',
        'request_body',
        'request_header',
        'response_body',
        'status_code'
    ];
}
