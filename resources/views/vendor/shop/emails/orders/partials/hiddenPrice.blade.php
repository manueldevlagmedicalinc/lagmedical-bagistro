@php($hidePayment = (bool) config('lagmedical.quote_mode.hide_checkout_payment', true))
@php($hideTotals = (bool) config('lagmedical.quote_mode.hide_totals', true))

<div style="margin-bottom: 34px;">
    <span style="font-size: 22px;font-weight: 600;color: #121A26">
        @lang('shop::app.emails.orders.created.title')
    </span> <br>

    <p style="font-size: 16px;color: #5E5E5E;line-height: 24px;">
        @lang('shop::app.emails.dear', ['customer_name' => $order->customer_full_name]),👋
    </p>

    <p style="font-size: 16px;color: #5E5E5E;line-height: 24px;">
        {!! trans('shop::app.emails.orders.created.greeting', [
                'order_id' => '<a href="' . route('shop.customers.account.orders.view', $order->id) . '" style="color: #2969FF;">#' . $order->increment_id . '</a>',
                'created_at' => core()->formatDate($order->created_at, 'Y-m-d H:i:s')
            ])
        !!}
    </p>

    <p style="font-size: 14px;color: #5E5E5E;line-height: 22px; margin-bottom: 20px;">
        {{ lagmedical_quote_message() }}
    </p>
</div>

<div style="font-size: 20px;font-weight: 600;color: #121A26">
    @lang('shop::app.emails.orders.created.summary')
</div>

<div style="display: flex;flex-direction: row;margin-top: 20px;justify-content: space-between;margin-bottom: 40px;">
    @if ($order->shipping_address)
        <div style="line-height: 25px;">
            <div style="font-size: 16px;font-weight: 600;color: #121A26;">
                @lang('shop::app.emails.orders.shipping-address')
            </div>

            <div style="font-size: 16px;font-weight: 400;color: #384860;margin-bottom: 40px;">
                {{ $order->shipping_address->company_name ?? '' }}<br/>
                {{ $order->shipping_address->name }}<br/>
                {{ $order->shipping_address->address }}<br/>
                {{ $order->shipping_address->postcode . " " . $order->shipping_address->city }}<br/>
                {{ $order->shipping_address->state }}<br/>
                ---<br/>
                @lang('shop::app.emails.orders.contact') : {{ $order->billing_address->phone }}
            </div>

            <div style="font-size: 16px;font-weight: 600;color: #121A26;">
                @lang('shop::app.emails.orders.shipping')
            </div>

            <div style="font-size: 16px;font-weight: 400;color: #384860;">
                {{ $order->shipping_title }}
            </div>
        </div>
    @endif

    @if ($order->billing_address)
        <div style="line-height: 25px;">
            <div style="font-size: 16px;font-weight: 600;color: #121A26;">
                @lang('shop::app.emails.orders.billing-address')
            </div>

            <div style="font-size: 16px;font-weight: 400;color: #384860;margin-bottom: 40px;">
                {{ $order->billing_address->company_name ?? '' }}<br/>
                {{ $order->billing_address->name }}<br/>
                {{ $order->billing_address->address }}<br/>
                {{ $order->billing_address->postcode . " " . $order->billing_address->city }}<br/>
                {{ $order->billing_address->state }}<br/>
                ---<br/>
                @lang('shop::app.emails.orders.contact') : {{ $order->billing_address->phone }}
            </div>

            @if (! $hidePayment)
                <div style="font-size: 16px;font-weight: 600;color: #121A26;">
                    @lang('shop::app.emails.orders.payment')
                </div>

                <div style="font-size: 16px;font-weight: 400;color: #384860;">
                    {{ core()->getConfigData('sales.payment_methods.' . $order->payment->method . '.title') }}
                </div>
            @endif
        </div>
    @endif
</div>

<div style="padding-bottom: 40px;border-bottom: 1px solid #CBD5E1;">
    <table style="overflow-x: auto; border-collapse: collapse; border-spacing: 0;width: 100%">
        <thead>
            <tr style="color: #121A26;border-top: 1px solid #CBD5E1;border-bottom: 1px solid #CBD5E1;">
                @foreach (['sku', 'name', 'qty'] as $item)
                    <th style="text-align: left;padding: 15px">@lang('shop::app.emails.orders.' . $item)</th>
                @endforeach
            </tr>
        </thead>
        <tbody style="font-size: 16px;font-weight: 400;color: #384860;">
            @foreach ($order->items as $item)
                <tr style="vertical-align: text-top;">
                    <td style="text-align: left;padding: 15px">{{ $item->getTypeInstance()->getOrderedItem($item)->sku }}</td>
                    <td style="text-align: left;padding: 15px">
                        {{ $item->name }}
                        @if (isset($item->additional['attributes']))
                            <div>
                                @foreach ($item->additional['attributes'] as $attribute)
                                    @if (! isset($attribute['attribute_type']) || $attribute['attribute_type'] !== 'file')
                                        <b>{{ $attribute['attribute_name'] }} : </b>{{ $attribute['option_label'] }}<br>
                                    @else
                                        {{ $attribute['attribute_name'] }} :
                                        <a href="{{ Storage::url($attribute['option_label']) }}" class="text-blue-600 hover:underline" download="{{ File::basename($attribute['option_label']) }}">{{ File::basename($attribute['option_label']) }}</a>
                                        <br>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </td>
                    <td style="text-align: left;padding: 15px">{{ $item->qty_ordered }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if (! $hideTotals)
    <div style="display: grid;justify-content: end;font-size: 16px;color: #384860;line-height: 30px;padding-top: 20px;padding-bottom: 20px;">
        <div style="display: grid;gap: 20px;grid-template-columns: repeat(2, minmax(0, 1fr));font-weight: bold"><span>@lang('shop::app.emails.orders.grand-total')</span><span style="text-align: right;">{{ core()->formatPrice($order->grand_total, $order->order_currency_code) }}</span></div>
    </div>
@endif
