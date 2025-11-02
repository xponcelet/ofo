<script setup>
import { Link } from '@inertiajs/vue3'
import { t } from '@/Composables/useTranslations'
</script>

<template>
    <section
        class="bg-white h-full flex flex-col rounded-2xl border border-gray-200 shadow-md hover:shadow-lg transition overflow-hidden"
    >
        <div class="p-6 text-center">
            <h2 class="text-2xl font-semibold">{{ t('random.title') }}</h2>
            <p class="text-gray-600 mt-1">{{ t('random.subtitle') }}</p>

            <!-- Image -->
            <div class="mt-6 overflow-hidden rounded-2xl border border-gray-100 shadow-inner">
                <img
                    src="https://images.unsplash.com/photo-1526778548025-fa2f459cd5c1?q=80&w=1200&auto=format&fit=crop"
                    :alt="t('random.alt')"
                    class="w-full h-56 object-cover transform hover:scale-105 transition duration-500 ease-out"
                />
            </div>
        </div>

        <!-- Bouton -->
        <div class="mt-auto p-6 pt-0 text-center">
            <Link
                :href="route('public.trips.random')"
                class="inline-flex items-center gap-2 rounded-full px-6 py-3 bg-blue-600 text-white font-medium shadow-sm hover:bg-blue-700 hover:shadow-md transition"
            >
                {{ t('random.button') }}
            </Link>
        </div>
    </section>
</template>
