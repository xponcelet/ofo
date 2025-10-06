<script setup>
import { Link } from '@inertiajs/vue3'
import { t } from '@/Composables/useTranslations'
</script>

<template>
    <section class="bg-white h-full flex flex-col rounded-2xl border border-gray-200 shadow-sm">
        <div class="p-6 text-center">
            <h2 class="text-2xl font-semibold">{{ t('random.title') }}</h2>
            <p class="text-gray-600 mt-1">{{ t('random.subtitle') }}</p>

            <div class="mt-5 mx-auto w-full">
                <div class="rounded-xl overflow-hidden border border-gray-200 bg-white aspect-[16/10]">
                    <img
                        src="https://images.unsplash.com/photo-1526778548025-fa2f459cd5c1?q=80&w=1200&auto=format&fit=crop"
                        :alt="t('random.alt')"
                        class="w-full h-full object-cover"
                    />
                </div>
            </div>
        </div>

        <div class="mt-auto p-6 pt-0 text-center">
            <Link
                :href="route('public.trips.random')"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2 bg-blue-600 text-white hover:bg-blue-700"
            >
                {{ t('random.button') }}
            </Link>
        </div>
    </section>
</template>
