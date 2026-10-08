<?php

namespace LagMedical\Eblast\Services;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Support\Facades\Log;
use LagMedical\Eblast\Contracts\EblastProviderInterface;
use RuntimeException;

class BrevoProvider implements EblastProviderInterface
{
    public function __construct(protected string $apiKey, protected ?ClientInterface $client = null)
    {
        $this->client ??= new Client([
            'base_uri' => 'https://api.brevo.com/v3/',
            'timeout' => 15,
            'http_errors' => false,
            'headers' => [
                'accept' => 'application/json',
                'api-key' => $this->apiKey,
                'content-type' => 'application/json',
            ],
        ]);
    }

    public function upsertContact(string $email, int $listId): void
    {
        $response = $this->client->post('contacts', [
            'json' => [
                'email' => $email,
                'listIds' => [$listId],
                'updateEnabled' => true,
            ],
        ]);

        Log::info('Brevo contact response.', [
            'status' => $response->getStatusCode(),
            'body' => (string) $response->getBody(),
            'list_id' => $listId,
        ]);

        if ($response->getStatusCode() >= 300) {
            throw new RuntimeException($this->errorMessage('Brevo rejected the contact.', $response));
        }
    }

    public function testConnection(int $listId): array
    {
        $accountResponse = $this->client->get('account');

        Log::info('Brevo account response.', [
            'status' => $accountResponse->getStatusCode(),
            'body' => (string) $accountResponse->getBody(),
        ]);

        if ($accountResponse->getStatusCode() >= 300) {
            throw new RuntimeException($this->errorMessage('Brevo rejected the account request.', $accountResponse));
        }

        $account = json_decode((string) $accountResponse->getBody(), true) ?: [];

        $listResponse = $this->client->get('contacts/lists/'.$listId);

        Log::info('Brevo list response.', [
            'status' => $listResponse->getStatusCode(),
            'body' => (string) $listResponse->getBody(),
            'list_id' => $listId,
        ]);

        if ($listResponse->getStatusCode() >= 300) {
            throw new RuntimeException($this->errorMessage('Brevo rejected the list request.', $listResponse));
        }

        $list = json_decode((string) $listResponse->getBody(), true) ?: [];

        return [
            'account' => [
                'email' => $account['email'] ?? null,
                'company' => $account['companyName'] ?? null,
            ],
            'list' => [
                'id' => (int) ($list['id'] ?? $listId),
                'name' => $list['name'] ?? null,
                'contacts' => $list['uniqueSubscribers'] ?? $list['totalSubscribers'] ?? null,
            ],
        ];
    }

    public function findOrCreateList(string $name, int $folderId): int
    {
        $offset = 0;

        do {
            $response = $this->client->get('contacts/lists', [
                'query' => ['limit' => 50, 'offset' => $offset],
            ]);
            $payload = json_decode((string) $response->getBody(), true);
            $lists = $payload['lists'] ?? [];

            foreach ($lists as $list) {
                if (strcasecmp($list['name'] ?? '', $name) === 0) {
                    return (int) $list['id'];
                }
            }

            $offset += count($lists);
        } while (count($lists) === 50);

        $response = $this->client->post('contacts/lists', [
            'json' => ['name' => $name, 'folderId' => $folderId],
        ]);
        $payload = json_decode((string) $response->getBody(), true);

        if (! isset($payload['id'])) {
            throw new RuntimeException('Brevo did not return the new list ID.');
        }

        return (int) $payload['id'];
    }

    protected function errorMessage(string $fallback, mixed $response): string
    {
        $body = (string) $response->getBody();
        $payload = json_decode($body, true);
        $message = $payload['message'] ?? $payload['code'] ?? null;

        Log::error('Brevo API request failed.', [
            'status' => $response->getStatusCode(),
            'body' => $body,
        ]);

        return $message
            ? $fallback.' ('.$response->getStatusCode().'): '.$message
            : $fallback.' ('.$response->getStatusCode().').';
    }

    public function findOrCreateFolder(string $name): int
    {
        $offset = 0;

        do {
            $response = $this->client->get('contacts/folders', [
                'query' => ['limit' => 50, 'offset' => $offset],
            ]);
            $payload = json_decode((string) $response->getBody(), true);
            $folders = $payload['folders'] ?? [];

            foreach ($folders as $folder) {
                if (strcasecmp($folder['name'] ?? '', $name) === 0) {
                    return (int) $folder['id'];
                }
            }

            $offset += count($folders);
        } while (count($folders) === 50);

        $response = $this->client->post('contacts/folders', [
            'json' => ['name' => $name],
        ]);
        $payload = json_decode((string) $response->getBody(), true);

        if (! isset($payload['id'])) {
            throw new RuntimeException('Brevo did not return the new folder ID.');
        }

        return (int) $payload['id'];
    }
}
