<?php

namespace App\Http\Resources\API\Inbound;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InboundResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        $expiryTime = null;

        if ($this->expiry_time > 0) {
            $expiryTime = Carbon::createFromTimestampMs($this->expiry_time)->format('Y-m-d H:i:s');
        }

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'up' => $this->up,
            'down' => $this->down,
            'total' => $this->total,
            'remark' => $this->remark,
            'enable' => $this->enable,
            'expiry_time' => $expiryTime,
            'listen' => $this->listen,
            'port' => $this->port,
            'protocol' => $this->protocol,
            'settings' => json_decode($this->settings, true),
            'stream_settings' => json_decode($this->stream_settings, true),
            'tag' => $this->tag,
            'sniffing' => json_decode($this->sniffing, true),
            'vmess' => $this->resource->vmess_link,
        ];
    }
}
