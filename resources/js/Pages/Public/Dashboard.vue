<script setup>
import { Head } from '@inertiajs/vue3'
import DashboardSearch from '@/Components/Public/DashboardSearch.vue'
import RandomTripCard from '@/Components/Public/RandomTripCard.vue'

const props = defineProps({ canCreate: { type: Boolean, default: false } })
</script>

<template>
    <Head title="Prépare un nouveau voyage" />

    <!-- FULL-BLEED: échappe le max-w du layout, uniquement pour cette section -->
    <section class="relative left-1/2 right-1/2 -mx-[50vw] w-screen bg-gray-50 overflow-x-hidden">
        <div class="mx-auto max-w-screen-2xl px-6 md:px-10 lg:px-14 py-12">
            <header class="mb-10 text-center">
                <h1 class="text-4xl md:text-5xl font-semibold tracking-tight">
                    Prépare un nouveau voyage
                </h1>
                <p class="mt-2 text-gray-600">Choisis ta destination ou laisse-toi surprendre.</p>
            </header>

            <!-- Éléments centrés, même largeur -->
            <div class="grid gap-8 md:grid-cols-2 items-stretch justify-items-stretch">
                <DashboardSearch :can-create="props.canCreate" />
                <RandomTripCard />
            </div>
        </div>
    </section>
</template>
