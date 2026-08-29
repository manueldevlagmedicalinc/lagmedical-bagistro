@php
    $channels = app('Webkul\Core\Repositories\ChannelRepository')->all();
    $selectedChannelIds = isset($category)
        ? app(\LagMedical\ChannelCategory\Services\CategoryChannelService::class)->selectedChannelIds($category->id)
        : [];
@endphp

<x-admin::accordion>
    <x-slot:header>
        <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
            Available Channels
        </p>
    </x-slot>

    <x-slot:content>
        <p class="mb-4 text-xs text-gray-500 dark:text-gray-300">
            Leave all unchecked to make this category visible in every channel. Select channels to restrict it.
        </p>

        @foreach ($channels as $channel)
            <x-admin::form.control-group class="!mb-2 flex items-center gap-2.5">
                <x-admin::form.control-group.control
                    type="checkbox"
                    :id="'channel_ids_' . $channel->id"
                    name="channel_ids[]"
                    :value="$channel->id"
                    :checked="in_array((int) $channel->id, $selectedChannelIds, true)"
                    :for="'channel_ids_' . $channel->id"
                    label="Available Channels"
                />

                <label
                    class="cursor-pointer text-xs font-medium text-gray-600 dark:text-gray-300"
                    for="channel_ids_{{ $channel->id }}"
                    v-pre
                >
                    {{ $channel->name }} <span class="text-gray-400">({{ $channel->code }})</span>
                </label>
            </x-admin::form.control-group>
        @endforeach
    </x-slot>
</x-admin::accordion>
