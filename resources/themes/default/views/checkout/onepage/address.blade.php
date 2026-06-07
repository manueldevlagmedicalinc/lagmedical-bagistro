{!! view_render_event('bagisto.shop.checkout.onepage.address.before') !!}

<!-- Accordion Blade Component -->
<div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
    <x-shop::accordion class="!border-0 !shadow-none">
        <!-- Accordion Header Component Slot -->
        <x-slot:header class="border-b border-zinc-200 px-6 py-5 max-md:px-4 max-md:py-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold tracking-tight max-md:text-base">
                    @lang('shop::app.checkout.onepage.address.title')
                </h2>
            </div>
        </x-slot>

        <!-- Accordion Content Component Slot -->
        <x-slot:content class="px-6 pb-6 pt-0 max-md:px-4 max-md:pb-4">
            <!-- If the customer is guest -->
            <template v-if="cart.is_guest">
                @include('shop::checkout.onepage.address.guest')
            </template>

            <!-- If the customer is logged in -->
            <template v-else>
                @include('shop::checkout.onepage.address.customer')
            </template>
        </x-slot:content>
    </x-shop::accordion>
</div>

{!! view_render_event('bagisto.shop.checkout.onepage.address.after') !!}
