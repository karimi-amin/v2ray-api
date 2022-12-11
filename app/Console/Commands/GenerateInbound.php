<?php

namespace App\Console\Commands;

use App\Models\Inbound\Inbound;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateInbound extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:inbound {--count=1} {--expire=} {--total=} {--protocol=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate inbound';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
//        for ($i=200; $i<=250; $i++) {
//            $ch = curl_init();
//
//            curl_setopt($ch, CURLOPT_URL, 'http://185.224.129.185:4780/xui/inbound/del/'. $i);
//            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//            curl_setopt($ch, CURLOPT_POST, 1);
//            curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
//
//            $headers = array();
//            $headers[] = 'Accept: application/json, text/plain, */*';
//            $headers[] = 'Accept-Language: en-US,en;q=0.9,fa;q=0.8';
//            $headers[] = 'Connection: keep-alive';
//            $headers[] = 'Content-Length: 0';
//            $headers[] = 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8';
//            $headers[] = 'Cookie: lang=en-US; session=MTY3MDc3NjU4MXxEdi1CQkFFQ180SUFBUkFCRUFBQVpmLUNBQUVHYzNSeWFXNW5EQXdBQ2t4UFIwbE9YMVZUUlZJWWVDMTFhUzlrWVhSaFltRnpaUzl0YjJSbGJDNVZjMlZ5XzRNREFRRUVWWE5sY2dIX2hBQUJBd0VDU1dRQkJBQUJDRlZ6WlhKdVlXMWxBUXdBQVFoUVlYTnpkMjl5WkFFTUFBQUFGUC1FRVFFQ0FRVmhaRzFwYmdFRllXUnRhVzRBfDP2R6CTq9vrtdEihsCXb6BqNiJcSSFDtFuC47_8R0sY';
//            $headers[] = 'Origin: http://185.224.129.185:4780';
//            $headers[] = 'Referer: http://185.224.129.185:4780/xui/inbounds';
//            $headers[] = 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36';
//            $headers[] = 'X-Requested-With: XMLHttpRequest';
//            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//
//            curl_exec($ch);
//            curl_close($ch);
//        }
//        dd(3);
        $count = $this->option('count') ?? 1;
        $expiryTime = !empty($this->option('expire')) ? Carbon::now()->addHour()->addDays($this->option('expire'))->getTimestampMs() : null;
        $total = $this->option('total') * 1024 * 1024 ?? 0;
        $protocol = $this->option('protocol') ?? 'vmess';

        $progressBar = $this->output->createProgressBar($count);

        for ($i = 0; $i < $count; $i++) {
            $this->create($expiryTime, $total, $protocol);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(1);

        return Command::SUCCESS;
    }

    protected function create($expiryTime = null, $total = 0, $protocol = 'vmess')
    {
        $uuid = Str::uuid()->toString();

        $settings = '{
  "clients": [
    {
      "id": "' . $uuid . '",
       "email": "",
            "expiryTime": "",
            "flow": "xtls-rprx-direct",
            "limitIp": 0,
            "totalGB": 0
    }
  ],
        "decryption": "none",
        "fallbacks": []
}';

        $streamSettings = '{
        "network": "ws",
        "security": "none",
        "wsSettings": {
          "acceptProxyProtocol": false,
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

        $port = rand(10000, 50000);

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
        $inbound->total = $total;
        $inbound->remark = $username ?? 'user_' . $port;
        $inbound->enable = 1;
        $inbound->listen = '';
        $inbound->expiry_time = $expiryTime;
        $inbound->port = $port;
        $inbound->protocol = $protocol;
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
          "acceptProxyProtocol": false,
          "path": "/",
          "headers": {}
        }
}'),
            'tag' => $inbound->tag,
            'sniffing' => json_decode($inbound->sniffing),
        ];

        File::put($configFilePath, json_encode($config, JSON_PRETTY_PRINT));

        return $inbound;
    }
}
