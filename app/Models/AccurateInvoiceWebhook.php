<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccurateInvoiceWebhook extends Model
{
    protected $table = 'accurate_invoice_webhooks';

    protected $fillable = [
        'webhook_id',
        'year',
        'data'
    ];
}
