<script setup>
import { Head } from '@inertiajs/vue3'
import DashboardSearch from '@/Components/Public/DashboardSearch.vue'
import RandomTripCard from '@/Components/Public/RandomTripCard.vue'
import Features from "@/Components/Features.vue";

const props = defineProps({
    canCreate: { type: Boolean, default: false },
})
</script>

<template>
    <Head title="Prépare ton prochain voyage" />

    <!-- HERO SECTION -->
    <section
        class="relative -mx-[50vw] left-1/2 w-screen overflow-hidden bg-gradient-to-br from-blue-50 via-sky-50 to-indigo-100"
    >
        <div class="relative mx-auto max-w-screen-2xl px-6 md:px-10 lg:px-16 py-20 text-center">
            <h1 class="text-4xl md:text-5xl font-semibold text-gray-900 tracking-tight">
                Prépare ton prochain voyage
            </h1>
            <p class="mt-3 text-lg text-gray-600 max-w-2xl mx-auto">
                Choisis ta destination, ou laisse-toi inspirer par un voyage aléatoire.
            </p>

            <!-- Image décorative douce -->
            <div
                class="absolute inset-0 opacity-10 pointer-events-none bg-[url('https://images.unsplash.com/photo-1502920917128-1aa500764b43?auto=format&fit=crop&w=1600&q=80')] bg-cover bg-center"
            ></div>
        </div>
    </section>

    <!-- CONTENU PRINCIPAL -->
    <section class="relative mx-auto max-w-screen-2xl px-6 md:px-10 lg:px-16 -mt-12 z-10 pb-16">
        <div class="grid gap-10 md:grid-cols-2 items-stretch">
            <DashboardSearch :can-create="props.canCreate" />
            <RandomTripCard />
        </div>
        <!-- Cards -->
        <div>
            <Features />
        </div>
    </section>
</template>
