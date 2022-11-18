<?php

namespace App\Repositories\API;

use App\Models\Inbound\Inbound;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class InboundRepository extends BaseAPIRepository
{
    public function __construct(Inbound $model)
    {
        $this->setModel($model);
    }

    public function getInbounds($request)
    {
        return Inbound::query()
            ->get();
    }

    public function create($request)
    {
        $username = $request->input('username');
        $expiryTime = $request->input('expiry_time') ?? null;
        $uuid = Str::uuid()->toString();

        $settings = '{
  "clients": [
    {
      "id": "' . $uuid . '",
      "alterId": 0
    }
  ],
  "disableInsecureEncryption": false
}';

        $streamSettings = '{
  "network": "ws",
  "security": "none",
  "wsSettings": {
    "path": "/",
    "headers": {}
  }
}';

        $sniffing = '{
  "enabled": true,
  "destOverride": [
    "http",
    "tls"
  ]
}';

        $port = rand(10000, 99999);

        $check = Inbound::query()
            ->where('port', $port)
            ->count();

        if ($check) {
            $port = rand(10000, 50000);
        }

        $inbound = new Inbound();
        $inbound->user_id = 1;
        $inbound->up = 0;
        $inbound->down = 0;
        $inbound->total = 0;
        $inbound->remark = 'user_' . $port;
        $inbound->enable = 1;
        $inbound->listen = '';
        $inbound->expiry_time = Carbon::parse($expiryTime)->getTimestampMs();
        $inbound->port = $port;
        $inbound->protocol = 'vmess';
        $inbound->settings = $settings;
        $inbound->stream_settings = $streamSettings;
        $inbound->tag = "inbound-{$port}";
        $inbound->sniffing = $sniffing;
        $inbound->save();

        $configFilePath = '/usr/local/x-ui/bin/config.json';
        $config = json_decode(File::get($configFilePath), true);
        $config['inbounds'][] = [
            'listen' => $inbound ?? null,
            'port' => $inbound->port,
            'protocol' => $inbound->protocol,
            'settings' => $inbound->settings,
            'streamSettings' => $inbound->stream_settings,
            'tag' => $inbound->tag,
            'sniffing' => $inbound->sniffing,
        ];

        File::put($configFilePath, json_encode($config, JSON_PRETTY_PRINT));

        return $inbound;
    }
}
