<?php

namespace LagMedical\Eblast\Services;

use Illuminate\Support\Facades\DB;
use LagMedical\Eblast\Contracts\EblastProviderInterface;
use RuntimeException;
use Webkul\Core\Repositories\ChannelRepository;

class EblastManager
{
    public function __construct(protected ChannelRepository $channelRepository) {}

    public function sync(string $email, int $channelId, string $listKey): void
    {
        $channel = $this->channelRepository->findOrFail($channelId);
        $prefix = 'emails.configure.eblast.';
        $config = fn (string $field) => core()->getConfigData($prefix.$field, $channel->code);

        if (! $config('enabled') || ! $config('api_key')) {
            throw new RuntimeException('Eblast/Brevo is not enabled or has no API key configured for this channel.');
        }

        if (ctype_digit($listKey) && (int) $listKey > 0) {
            $this->provider((string) $config('api_key'))->upsertContact($email, (int) $listKey);

            return;
        }

        $lists = json_decode((string) $config('lists'), true) ?: [];
        $list = collect($lists)->first(fn (array $item) => ($item['key'] ?? null) === $listKey);

        if ($listKey === 'frames-campaign-landing-list') {
            $list = array_merge([
                'key' => $listKey,
                'name' => 'Landing-LagMedical-Campaing',
                'id' => 9,
            ], $list ?: []);

            $list['id'] = (int) ($list['id'] ?: 9);
        }

        if (! $list) {
            throw new RuntimeException("Eblast list [{$listKey}] is not configured.");
        }

        $provider = $this->provider((string) $config('api_key'));
        $listId = (int) ($list['id'] ?? 0);

        if (! $listId) {
            $folderId = $provider->findOrCreateFolder((string) $config('folder_name'));
            $listId = $provider->findOrCreateList(
                (string) ($list['name'] ?? $listKey),
                $folderId
            );

            $lists = collect($lists)->map(function (array $item) use ($listKey, $listId) {
                if (($item['key'] ?? null) === $listKey) {
                    $item['id'] = $listId;
                }

                return $item;
            })->values()->all();

            $this->saveListConfiguration($lists, $channel->code);
        }

        $provider->upsertContact($email, $listId);
    }

    public function testConnection(string $apiKey, int $listId): array
    {
        return (new BrevoProvider($apiKey))->testConnection($listId);
    }

    protected function provider(string $apiKey): EblastProviderInterface
    {
        return new BrevoProvider($apiKey);
    }

    protected function saveListConfiguration(array $lists, string $channelCode): void
    {
        $config = DB::table('core_config')
            ->where('code', 'emails.configure.eblast.lists')
            ->where('channel_code', $channelCode)
            ->first();

        if ($config) {
            DB::table('core_config')->where('id', $config->id)->update([
                'value' => json_encode($lists),
            ]);
        }
    }
}
