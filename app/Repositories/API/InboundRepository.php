<?php

namespace App\Repositories\API;

use App\Models\Inbound\Inbound;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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
            ->orderBy('id', 'desc')
            ->limit(2)
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
            'listen' => !empty($inbound->listen) ? $inbound->listen : null,
            'port' => $inbound->port,
            'protocol' => $inbound->protocol,
            'settings' => json_decode($inbound->settings),
            'streamSettings' => json_decode('{
  "network": "ws",
  "security": "none",
  "wsSettings": {
    "path": "/",
    "headers": []
  }
}'),
            'tag' => $inbound->tag,
            'sniffing' => json_decode($inbound->sniffing),
        ];

        File::put($configFilePath, json_encode($config, JSON_PRETTY_PRINT));

        return $inbound;
    }

    protected function restartPanel()
    {
        $ip = file_get_contents('https://api.ipify.org');
        $port = DB::table('settings')->where('key', 'webPort')->value('value') ?: 54321;
        $curl = curl_init();
        $url = "http://{$ip}:{$port}/xui/setting/restartPanel";
        dd($url);
        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://{$ip}:{$port}/xui/setting/restartPanel",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                'Cookie: session=MTY2Nzc1ODk2M3xEdi1CQkFFQ180SUFBUkFCRUFBQVpmLUNBQUVHYzNSeWFXNW5EQXdBQ2t4UFIwbE9YMVZUUlZJWWVDMTFhUzlrWVhSaFltRnpaUzl0YjJSbGJDNVZjMlZ5XzRNREFRRUVWWE5sY2dIX2hBQUJBd0VDU1dRQkJBQUJDRlZ6WlhKdVlXMWxBUXdBQVFoUVlYTnpkMjl5WkFFTUFBQUFGXy1FRkFFQ0FRUmhiV2x1QVFreE1URXhNVEV4TVRFQXzkRRPjIlvWvVZ3GEZ5MJ5VJUIA9jLgM02NWZGq2I7xfg=='
            ),
        ));

        $response = curl_exec($curl);
        dd($response);
        curl_close($curl);
        echo $response;
    }
}
