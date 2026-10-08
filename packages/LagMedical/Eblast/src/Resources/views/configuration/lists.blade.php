@php
    $channel = core()->getCurrentChannel();
    $value = system_config()->getConfigData($field->getNameKey(), $channel->code, app()->getLocale());
    $lists = json_decode((string) $value, true) ?: [['key' => 'frames-campaign-landing-list', 'name' => 'Landing-LagMedical-Campaing', 'id' => 9]];
    $fieldName = $field->getNameField();
@endphp

<div
    class="eblast-lists-config"
    data-eblast-connection
    data-test-url="{{ route('admin.eblast.connection.test') }}"
>
    <input type="hidden" name="keys[]" value="{{ json_encode($child) }}">

    <p class="mb-3 text-sm text-gray-600 dark:text-gray-300">
        La landing Frames utiliza el ID configurado en <strong>Frames campaign list ID</strong>.
    </p>

    <button
        type="button"
        class="secondary-button"
        data-eblast-test-connection
    >
        Probar conexión con Brevo
    </button>

    <p
        class="mt-2 text-sm"
        data-eblast-connection-result
        aria-live="polite"
    ></p>

    <input
        type="hidden"
        name="{{ $fieldName }}"
        value="{{ e(json_encode($lists)) }}"
    >
</div>

<script src="{{ asset('assets/eblast-connection.js') }}" defer></script>
