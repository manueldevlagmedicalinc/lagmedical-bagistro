{!! view_render_event('bagisto.shop.checkout.onepage.shipping_methods.before') !!}

<v-shipping-methods
    :methods="shippingMethods"
    @processing="stepForward"
    @processed="stepProcessed"
>
    <!-- Shipping Method Shimmer Effect -->
    <x-shop::shimmer.checkout.onepage.shipping-method />
</v-shipping-methods>

{!! view_render_event('bagisto.shop.checkout.onepage.shipping_methods.after') !!}

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-shipping-methods-template"
    >
        <div class="mb-7 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm max-md:mb-0 max-md:rounded-xl">
            <template v-if="! methods">
                <!-- Shipping Method Shimmer Effect -->
                <x-shop::shimmer.checkout.onepage.shipping-method />
            </template>

            <template v-else>
                <!-- Accordion Blade Component -->
                <x-shop::accordion class="overflow-hidden !border-0 !shadow-none max-md:!bg-transparent">
                    <!-- Accordion Blade Component Header -->
                    <x-slot:header class="border-b border-zinc-200 px-6 py-5 max-md:border-b-0 max-md:px-4 max-md:py-4 max-md:text-sm max-md:font-medium">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-semibold tracking-tight max-md:text-base">
                                @lang('shop::app.checkout.onepage.shipping.shipping-method')
                            </h2>
                        </div>
                    </x-slot>

                    <!-- Accordion Blade Component Content -->
                    <x-slot:content class="px-6 pb-6 pt-0 max-md:px-4 max-md:pb-4 max-md:pt-0">
                        <div class="flex flex-wrap gap-4 max-md:gap-3">
                            <template v-for="method in methods">
                                {!! view_render_event('bagisto.shop.checkout.onepage.shipping_method.before') !!}

                                <div
                                    class="relative min-w-[240px] flex-1 select-none max-md:min-w-0 max-md:flex-auto"
                                    v-for="rate in method.rates"
                                >
                                    <input 
                                        type="radio"
                                        name="shipping_method"
                                        :id="rate.method"
                                        :value="rate.method"
                                        class="peer hidden"
                                        @change="store(rate.method)"
                                    >

                                    <label 
                                        class="icon-radio-unselect peer-checked:icon-radio-select absolute top-5 cursor-pointer text-2xl text-navyBlue ltr:right-5 rtl:left-5"
                                        :for="rate.method"
                                    >
                                    </label>

                                    <label 
                                        class="block cursor-pointer rounded-2xl border border-zinc-200 bg-white p-5 transition duration-200 hover:border-navyBlue/30 hover:bg-slate-50 peer-checked:border-navyBlue peer-checked:bg-blue-50/40 peer-checked:ring-1 peer-checked:ring-navyBlue/20 max-sm:flex max-sm:gap-4 max-sm:rounded-xl max-sm:px-4 max-sm:py-3"
                                        :for="rate.method"
                                    >
                                        <span class="icon-flate-rate text-6xl text-navyBlue max-sm:text-5xl"></span>

                                        <div>
                                            @if (! lagmedical_hide_shipping_amounts())
                                                <p class="mt-1.5 text-2xl font-semibold max-md:text-base">
                                                    @{{ rate.base_formatted_price }}
                                                </p>
                                            @endif
                                            
                                            <p class="mt-2.5 text-xs font-medium max-md:mt-1 max-sm:mt-0 max-sm:font-normal max-sm:text-zinc-500">
                                                <span class="font-medium">@{{ rate.method_title }}</span> - @{{ rate.method_description }}
                                            </p>
                                        </div>
                                    </label>
                                </div>

                                {!! view_render_event('bagisto.shop.checkout.onepage.shipping_method.after') !!}
                            </template>
                        </div>
                    </x-slot>
                </x-shop::accordion>
            </template>
        </div>
    </script>

    <script type="module">
        app.component('v-shipping-methods', {
            template: '#v-shipping-methods-template',

            props: {
                methods: {
                    type: Object,
                    required: true,
                    default: () => null,
                },
            },

            emits: ['processing', 'processed'],

            methods: {
                store(selectedMethod) {
                    this.$emit('processing', 'payment');

                    this.$axios.post("{{ route('shop.checkout.onepage.shipping_methods.store') }}", {    
                            shipping_method: selectedMethod,
                        })
                        .then(response => {
                            if (response.data.redirect_url) {
                                window.location.href = response.data.redirect_url;
                            } else {
                                this.$emit('processed', response.data.payment_methods);
                            }
                        })
                        .catch(error => {
                            this.$emit('processing', 'shipping');

                            if (error.response.data.redirect_url) {
                                window.location.href = error.response.data.redirect_url;
                            }
                        });
                },
            },
        });
    </script>
@endPushOnce
