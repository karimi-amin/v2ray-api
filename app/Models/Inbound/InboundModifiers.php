<?php

namespace App\Models\Inbound;

trait InboundModifiers
{
    public function getVmessLinkAttribute()
    {
        $settings = json_decode($this->settings, true);
        $streamSettings = json_decode($this->stream_settings, true);

        $obj = [
            'v' => '2',
            'ps' => $this->remark,
            'add' => file_get_contents('https://api.ipify.org'),
            'port' => $this->port,
            'id' => $settings['clients'][0]['id'],
            'aid' => $settings['clients'][0]['alterId'],
            'net' => $streamSettings['network'],
            'type' => 'none',
            'host' => '',
            'path' => $streamSettings['wsSettings']['path'],
            'tls' => $streamSettings['security'],
        ];

        $json = json_encode($obj, JSON_PRETTY_PRINT, 2);
        $vmess = 'vmess://' . base64_encode($json);

        return $vmess;
    }
}
