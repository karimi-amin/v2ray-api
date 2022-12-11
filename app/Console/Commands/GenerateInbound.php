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
    protected $signature = 'generate:inbound {--count=1}';

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
        $count = $this->option('count') ?? 1;

        $progressBar = $this->output->createProgressBar($count);

        for ($i = 0; $i < $count; $i++) {
            $this->create();
            $progressBar->advance();
        }

        $progressBar->finish();
    }

    protected function create()
    {
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
        $inbound->total = 0;
        $inbound->remark = $username ?? 'user_' . $port;
        $inbound->enable = 1;
        $inbound->listen = '';
        $inbound->expiry_time = null;
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
}
