<?php

namespace Dock\A11yChecker;

use DateTimeInterface;
use Dock\A11yChecker\Enums\AuditStatus;
use Dock\A11yChecker\Enums\Device;
use Dock\A11yChecker\Enums\Language;
use Dock\A11yChecker\Enums\Sort;
use Dock\A11yChecker\Dtos\HistoryFilters;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

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
     * @param ?string $uniqueKey
     * @return array
     * @throws GuzzleException
     */
    public function scan(string $url, Language $lang = Language::EN, Device $device = Device::DESKTOP, bool $sync = false, bool $extraData = false, ?string $uniqueKey = null): array
    {
        $data = [
            'url' => $url,
            'sync' => $sync,
            'lang' => $lang,
            'extra_data' => $extraData,
            'unique_key' => $uniqueKey,
        ];
        if ($device !== Device::ALL) {
            $data['device'] = $device;
        }
        return $this->request("scan", $data);
    }

    /**
     * Run new scan
     * @param string $uuid
     * @param Language $lang
     * @param bool $sync
     * @param bool $extraData
     * @return array
     * @throws GuzzleException
     */
    public function rescan(string $uuid, Language $lang = Language::EN, bool $sync = false, bool $extraData = false): array
    {
        $data = [
            'uuid' => $uuid,
            'sync' => $sync,
            'lang' => $lang,
            'extra_data' => $extraData,
        ];
        return $this->request("rescan", $data);
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
     * Get multi audits
     * @param string $search
     * @param int $page
     * @param int $perPage
     * @param Sort $sort
     * @param ?string $uniqueKey
     * @return array
     * @throws GuzzleException
     */
    public function audits(string $search, int $page = 1, int $perPage = 10, Sort $sort = Sort::LAST_AUDIT_DESC, ?string $uniqueKey = null): array
    {
        return $this->request("audits", [
            'search' => $search,
            'page' => $page,
            'per_page' => $perPage,
            'sort' => $sort,
            'unique_key' => $uniqueKey ?? '',
        ]);
    }

    /**
     * Get audit history
     * @param string $uuid
     * @param int $page
     * @param int $perPage
     * @param Sort $sort
     * @param ?HistoryFilters $filters
     * @return array
     * @throws GuzzleException
     */
    public function history(string $uuid, int $page = 1, int $perPage = 10, Sort $sort = Sort::CREATED_AT_ASC, ?HistoryFilters $filters = null): array
    {
        $params = [
            'uuid' => $uuid,
            'page' => $page,
            'per_page' => $perPage,
            'sort' => $sort,
        ];
        if ($filters) {
            if ($filters->date_from) {
                $params['date_from'] = $filters->date_from instanceof \DateTime ? $filters->date_from->format(DateTimeInterface::ATOM) : $filters->date_from;
            }
            if ($filters->date_to) {
                $params['date_to'] = $filters->date_to instanceof \DateTime ? $filters->date_to->format(DateTimeInterface::ATOM) : $filters->date_to;
            }
        }
        return $this->request("history", $params);
    }

    /**
     * Get current user data
     * @return array
     * @throws GuzzleException
     */
    public function user(): array
    {
        return $this->request("user");
    }

    /**
     * Delete audit
     * @param string $uuid
     * @return array
     * @throws GuzzleException
     */
    public function deleteAudit(string $uuid): array
    {
        return $this->request("audit", [
            'uuid' => $uuid,
        ], method: 'delete');
    }

    /**
     * Delete history
     * @param string $uuid
     * @return array
     * @throws GuzzleException
     */
    public function deleteHistory(string $uuid): array
    {
        return $this->request("history", [
            'uuid' => $uuid,
        ], method: 'delete');
    }

    /**
     * Update audit manual
     * @param string $uuid
     * @param string $criterionId
     * @param AuditStatus $status
     * @param Device $device
     * @return array
     * @throws GuzzleException
     */
    public function updateAuditManual(string $uuid, string $criterionId, AuditStatus $status, Device $device = Device::DESKTOP): array
    {
        return $this->request("audit/manual", [
            'uuid' => $uuid,
            'criterion_id' => $criterionId,
            'status' => $status,
            'device' => $device,
        ], method: 'post');
    }

    /**
     * @param string $endpoint
     * @param array $params
     * @param array $headers
     * @param string $method
     * @return array
     * @throws GuzzleException
     */
    protected function request(string $endpoint, array $params = [], array $headers = [], string $method = 'get'): array
    {
        if ($this->apiKey) {
            $params['key'] = $this->apiKey;
        }
        $params['t'] = intdiv(time(), 10);
        if (empty($headers['Accept'])) {
            $headers['Accept'] = 'application/json';
        }
        try {
            $endpoint = ltrim($endpoint, '/');
            if ($method === 'post') {
                $dataType = str_contains($endpoint, 'token') ? 'form_params' : 'json';
                if ($dataType === 'json') {
                    if (empty($headers['Content-Type'])) {
                        $headers['Content-Type'] = 'application/json';
                    }
                }
                $response = $this->http->post("api/$endpoint", ['headers' => $headers, $dataType => $params]);
            } else {
                $query = http_build_query(array_filter($params));
                $response = $this->http->$method("api/$endpoint?$query", ['headers' => $headers]);
            }
            $status = $response->getStatusCode();
            $body = json_decode($response->getBody()->getContents(), true) ?: [];
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $status = $response->getStatusCode();
                $body = (string)$response->getBody();
                if (!is_numeric($body) && json_validate($body)) {
                    $body = json_decode($body, true) ?: [];
                }
            } else {
                throw $e;
            }
        }
        return ['response' => $body, 'status' => $status];
    }
}
