<?php

if (! function_exists('lagmedical_is_quote_mode')) {
    function lagmedical_is_quote_mode(): bool
    {
        if (! config('lagmedical.enabled', true)) {
            return false;
        }

        $channelCode = core()->getCurrentChannel()?->code;

        if (! $channelCode) {
            return false;
        }

        return in_array($channelCode, config('lagmedical.quote_mode_channels', []), true);
    }
}

if (! function_exists('lagmedical_quote_mode_value')) {
    function lagmedical_quote_mode_value(string $key, mixed $default = null): mixed
    {
        return config('lagmedical.quote_mode.'.$key, $default);
    }
}

if (! function_exists('lagmedical_localized')) {
    function lagmedical_localized(mixed $value, string $default): string
    {
        if (! is_array($value)) {
            return is_string($value) ? $value : $default;
        }

        $locale = app()->getLocale();

        if (! empty($value[$locale])) {
            return (string) $value[$locale];
        }

        if (! empty($value['en'])) {
            return (string) $value['en'];
        }

        return $default;
    }
}

if (! function_exists('lagmedical_hide_prices')) {
    function lagmedical_hide_prices(): bool
    {
        return lagmedical_is_quote_mode() && (bool) lagmedical_quote_mode_value('hide_prices', true);
    }
}

if (! function_exists('lagmedical_hide_totals')) {
    function lagmedical_hide_totals(): bool
    {
        return lagmedical_is_quote_mode() && (bool) lagmedical_quote_mode_value('hide_totals', true);
    }
}

if (! function_exists('lagmedical_hide_checkout_payment')) {
    function lagmedical_hide_checkout_payment(): bool
    {
        return lagmedical_is_quote_mode() && (bool) lagmedical_quote_mode_value('hide_checkout_payment', true);
    }
}

if (! function_exists('lagmedical_hide_coupons')) {
    function lagmedical_hide_coupons(): bool
    {
        return lagmedical_is_quote_mode() && (bool) lagmedical_quote_mode_value('hide_coupons', true);
    }
}

if (! function_exists('lagmedical_hide_tax')) {
    function lagmedical_hide_tax(): bool
    {
        return lagmedical_is_quote_mode() && (bool) lagmedical_quote_mode_value('hide_tax', true);
    }
}

if (! function_exists('lagmedical_hide_shipping_amounts')) {
    function lagmedical_hide_shipping_amounts(): bool
    {
        return lagmedical_is_quote_mode() && (bool) lagmedical_quote_mode_value('hide_shipping_amounts', true);
    }
}

if (! function_exists('lagmedical_quote_message')) {
    function lagmedical_quote_message(): string
    {
        return lagmedical_localized(
            lagmedical_quote_mode_value('review_message', []),
            'Orders are reviewed and confirmed by our sales department before processing.'
        );
    }
}

if (! function_exists('lagmedical_quote_button_label')) {
    function lagmedical_quote_button_label(): string
    {
        return lagmedical_localized(
            lagmedical_quote_mode_value('cart_button_label', []),
            'Submit Order Request'
        );
    }
}

if (! function_exists('lagmedical_checkout_button_label')) {
    function lagmedical_checkout_button_label(): string
    {
        return lagmedical_localized(
            lagmedical_quote_mode_value('checkout_button_label', []),
            'Submit Order Request'
        );
    }
}
