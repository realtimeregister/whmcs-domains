<?php

namespace RealtimeRegister\Services;

use Illuminate\Database\Capsule\Manager as Capsule;
use RealtimeRegister\App;

class UpdateService
{
    private string $releaseUrl = 'https://api.github.com/repos/realtimeregister/whmcs/releases';

    public function check(): void
    {
        $headers = [];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->releaseUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Realtime Register WHMCS Client/' . App::VERSION);
        curl_setopt(
            $ch,
            CURLOPT_HEADERFUNCTION,
            function ($curl, $header) use (&$headers) {
                $len = strlen($header);
                $header = explode(':', $header, 2);
                // ignore invalid headers
                if (count($header) < 2) {
                    return $len;
                }

                $headers[strtolower(trim($header[0]))][] = trim($header[1]);

                return $len;
            }
        );
        $response = curl_exec($ch);
        curl_close($ch);

        // See if we got any results, we could be rate limited..
        if (array_key_exists('x-ratelimit-remaining', $headers) && $headers['x-ratelimit-remaining'][0] > 0) {
            $results = json_decode($response);
            $latestVersion = [];
            foreach ($results as $result) {
                if (!$result->draft && $result->prerelease === true) {
                    $latestVersion = [
                        'version' => $result->tag_name,
                        'prerelease' => $result->prerelease,
                        'description' => nl2br($result->body),
                        'link' => $result->html_url,
                    ];
                    break;
                }
            }

            if (!empty($latestVersion)) {
                $attributes = Capsule::table('tblregistrars')
                    ->where('registrar', 'realtimeregister')
                    ->where('setting', 'version_information');

                $values = [
                    'registrar' => 'realtimeregister',
                    'setting' => 'version_information',
                    'value' => json_encode($latestVersion),
                ];
                if (!$attributes->exists()) {
                    Capsule::table('tblregistrars')->insert($values);
                } else {
                    $attributes->update($values);
                }
            } else {
                Capsule::table('tblregistrars')
                    ->where('registrar', 'realtimeregister')
                    ->where('setting', 'version_information')
                    ->delete();
            }
        }
    }
}
