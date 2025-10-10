<?php

namespace Dock\A11yChecker;

use Dock\A11yChecker\Enums\Device;
use Dock\A11yChecker\Enums\Language;
use Dock\A11yChecker\Enums\Sort;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;

class Client
{
    private GuzzleClient $http;

    public function __construct(private readonly ?string $apiKey = null, ?string $baseUrl = null)
    {
        $this->http = new GuzzleClient([
            'base_uri' => rtrim($baseUrl ?: 'https://a11y-checker.wcag.dock.codes', '/') . '/',
            'headers' => [
                'Accept' => 'application/json',
            ],
            'timeout' => 300,
        ]);
    }

    /**
     * Run new scan
     * @param string $url
     * @param Language $lang
     * @param Device $device
     * @param bool $sync
     * @param bool $extraData
     * @return array
     * @throws GuzzleException
     */
    public function scan(string $url, Language $lang = Language::EN, Device $device = Device::DESKTOP, bool $sync = false, bool $extraData = false): array
    {
        $data = [
            'url' => $url,
            'sync' => $sync,
            'lang' => $lang,
            'extra_data' => $extraData,
        ];
        if ($device !== Device::ALL) {
            $data['device'] = $device;
        }
        return $this->request("scan", $data);
    }

    /**
     * Get audit
     * @param string $uuid
     * @param Language $lang
     * @param bool $extraData
     * @return array
     * @throws GuzzleException
     */
    public function audit(string $uuid, Language $lang = Language::EN, bool $extraData = false): array
    {
        return $this->request("audit", [
            'uuid' => $uuid,
            'lang' => $lang,
            'extra_data' => $extraData,
        ]);
    }

    /**
     * Get audit history
     * @param string $uuid
     * @param int $page
     * @param int $perPage
     * @param Sort $sort
     * @return array
     * @throws GuzzleException
     */
    public function history(string $uuid, int $page = 1, int $perPage = 10, Sort $sort = Sort::CREATED_AT_ASC): array
    {
        return $this->request("history", [
            'uuid' => $uuid,
            'page' => $page,
            'per_page' => $perPage,
            'sort' => $sort,
        ]);
    }

    /**
     * @param string $endpoint
     * @param array $params
     * @return array
     * @throws GuzzleException
     */
    private function request(string $endpoint, array $params = []): array
    {
        if ($this->apiKey) {
            $params['key'] = $this->apiKey;
        }
        $params['t'] = intdiv(time(), 10);
        $query = http_build_query(array_filter($params));
        $response = $this->http->get("api/$endpoint?$query");
        return json_decode($response->getBody()->getContents(), true) ?: [];
    }
}
