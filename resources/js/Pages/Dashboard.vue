<script setup>
import { Head, usePage } from '@inertiajs/vue3'
import DashboardSearch from '@/Components/Public/DashboardSearch.vue'
import RandomTripCard from '@/Components/Public/RandomTripCard.vue'
import { t } from '@/Composables/useTranslations.js'
import Features from "@/Components/Features.vue";

const props = defineProps({
    canCreate: { type: Boolean, default: false }
})

const page = usePage()
</script>

<template>
    <Head :title="t('dashboard.title')" />

    <main class="flex-1 bg-surface text-on-surface">
        <section class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- En-tête -->
            <header class="mb-12 text-center">
                <h1 class="text-4xl md:text-5xl font-bold tracking-tight text-primary">
                    {{ t('dashboard.title') }}
                </h1>
                <p class="mt-3 text-lg text-on-surface-variant">
                    {{ t('dashboard.subtitle') }}
                </p>
            </header>

            <!-- Grille de choix -->
            <div class="grid gap-8 md:grid-cols-2">
                <DashboardSearch :can-create="props.canCreate" />
                <RandomTripCard />
            </div>
            <!-- Cards -->
            <div>
                <Features />
            </div>
        </section>
    </main>
</template>
