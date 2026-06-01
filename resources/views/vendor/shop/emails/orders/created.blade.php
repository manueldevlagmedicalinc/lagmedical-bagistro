@component('shop::emails.layout')
    @php($channelCode = $order->channel?->code)
    @php($isQuoteChannel = in_array((string) $channelCode, config('lagmedical.quote_mode_channels', []), true))
    @php($useHiddenPriceTemplate = config('lagmedical.enabled', true) && $isQuoteChannel)

    @include(
        $useHiddenPriceTemplate
            ? 'shop::emails.orders.partials.hiddenPrice'
            : 'shop::emails.orders.partials.defaultPrice',
        ['order' => $order]
    )
@endcomponent
