<?php

namespace App\Models\Inbound;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inbound extends BaseModel
{
    use InboundRelationships,
        InboundScopes,
        InboundModifiers;

    protected $table = 'inbounds';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'user_id',
        'up',
        'down',
        'total',
        'remark',
        'enable',
        'expiry_time',
        'listen',
        'port',
        'protocol',
        'settings',
        'stream_settings',
        'tag',
        'sniffing',
    ];
}
