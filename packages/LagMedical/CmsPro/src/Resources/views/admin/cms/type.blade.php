@php
    $cmsProType = isset($page) ? $cmsProService->pageType($page->id) : old('cms_pro_type', 'native');
@endphp

<div class="box-shadow mb-2.5 rounded bg-white p-4 dark:bg-gray-900">
    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">Tipo de página</p>

    <x-admin::form.control-group class="!mb-0">
        <x-admin::form.control-group.label>Editor</x-admin::form.control-group.label>
        <x-admin::form.control-group.control
            type="select"
            name="cms_pro_type"
            :value="$cmsProType"
            label="Editor"
        >
            <option value="native" @selected($cmsProType === 'native')>Editor tradicional</option>
            <option value="cms_pro" @selected($cmsProType === 'cms_pro')>CMS Pro</option>
        </x-admin::form.control-group.control>
        <p class="mt-2 text-xs text-gray-500">La página puede utilizar el editor tradicional o CMS Pro.</p>
    </x-admin::form.control-group>
</div>
