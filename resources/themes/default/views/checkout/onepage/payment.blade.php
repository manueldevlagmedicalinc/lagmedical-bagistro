{!! view_render_event('bagisto.shop.checkout.onepage.payment_methods.before') !!}

<v-payment-methods
    :methods="paymentMethods"
    @processing="stepForward"
    @processed="stepProcessed"
>
    <x-shop::shimmer.checkout.onepage.payment-method />
</v-payment-methods>

{!! view_render_event('bagisto.shop.checkout.onepage.payment_methods.after') !!}

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-payment-methods-template"
    >
        <div class="mb-7 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm max-md:mb-0 max-md:rounded-xl">
            <template v-if="! methods">
                <!-- Payment Method shimmer Effect -->
                <x-shop::shimmer.checkout.onepage.payment-method />
            </template>

            <template v-else>
                {!! view_render_event('bagisto.shop.checkout.onepage.payment_method.accordion.before') !!}

                <!-- Checkout Step Accordion: Payment Method -->
                <x-shop::accordion class="!border-0 !shadow-none">
                    <!-- Payment Method Accordion Header -->
                    <x-slot:header class="border-b border-zinc-200 px-6 py-5 max-md:border-b-0 max-md:px-4 max-md:py-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-semibold tracking-tight max-md:text-base">
                                @lang('shop::app.checkout.onepage.payment.payment-method')
                            </h2>
                        </div>
                    </x-slot>

                    <!-- Payment Method Accordion Content -->
                    <x-slot:content class="px-6 pb-6 pt-3 max-md:px-4 max-md:pb-4">
                        <div class="flex flex-wrap gap-4 max-md:gap-3">
                            <div 
                                class="relative min-w-[220px] flex-1 cursor-pointer max-md:min-w-0 max-md:flex-auto"
                                v-for="(payment, index) in methods"
                            >
                                {!! view_render_event('bagisto.shop.checkout.payment-method.before') !!}

                                <input 
                                    type="radio" 
                                    name="payment[method]" 
                                    :value="payment.payment"
                                    :id="payment.method"
                                    class="peer hidden"
                                    @change="store(payment)"
                                >

                                <label 
                                    :for="payment.method" 
                                    class="icon-radio-unselect peer-checked:icon-radio-select absolute top-5 cursor-pointer text-2xl text-navyBlue ltr:right-5 rtl:left-5"
                                >
                                </label>

                                <label 
                                    :for="payment.method" 
                                    class="flex h-full min-h-[142px] cursor-pointer items-start gap-4 rounded-2xl border border-zinc-200 bg-white p-5 transition duration-200 hover:border-navyBlue/30 hover:bg-slate-50 peer-checked:border-navyBlue peer-checked:bg-blue-50/40 peer-checked:ring-1 peer-checked:ring-navyBlue/20 max-md:min-h-0 max-md:rounded-xl max-md:px-4 max-md:py-3"
                                >
                                    {!! view_render_event('bagisto.shop.checkout.onepage.payment-method.image.before') !!}

                                    <img
                                        class="max-h-11 max-w-14"
                                        :src="payment.image"
                                        width="55"
                                        height="55"
                                        :alt="payment.method_title"
                                        :title="payment.method_title"
                                    />

                                    {!! view_render_event('bagisto.shop.checkout.onepage.payment-method.image.after') !!}

                                    <div class="min-w-0">
                                        {!! view_render_event('bagisto.shop.checkout.onepage.payment-method.title.before') !!}

                                        <p class="mt-1.5 text-sm font-semibold text-navyBlue max-md:mt-1">
                                            @{{ payment.method_title }}
                                        </p>
                                        
                                        {!! view_render_event('bagisto.shop.checkout.onepage.payment-method.title.after') !!}

                                        {!! view_render_event('bagisto.shop.checkout.onepage.payment-method.description.before') !!}

                                        <p class="mt-2.5 text-xs font-medium leading-5 text-zinc-500 max-md:mt-1">
                                            @{{ payment.description }}
                                        </p> 

                                        {!! view_render_event('bagisto.shop.checkout.onepage.payment-method.description.after') !!}
                                    </div>
                                </label>

                                {!! view_render_event('bagisto.shop.checkout.payment-method.after') !!}

                            </div>
                        </div>
                    </x-slot>
                </x-shop::accordion>

                {!! view_render_event('bagisto.shop.checkout.onepage.payment_method.accordion.after') !!}
            </template>
        </div>
    </script>

    <script type="module">
        app.component('v-payment-methods', {
            template: '#v-payment-methods-template',

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
                    this.$emit('processing', 'review');

                    this.$axios.post("{{ route('shop.checkout.onepage.payment_methods.store') }}", {
                            payment: selectedMethod
                        })
                        .then(response => {
                            this.$emit('processed', response.data.cart);
                        })
                        .catch(error => {
                            this.$emit('processing', 'payment');

                            if (error.response.data.redirect_url) {
                                window.location.href = error.response.data.redirect_url;
                            }
                        });
                },
            },
        });
    </script>
@endPushOnce
