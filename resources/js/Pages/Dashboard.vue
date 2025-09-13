<script setup>
import { Head } from '@inertiajs/vue3'


const props = defineProps({
    canCreate: { type: Boolean, default: false },
})
</script>

<template>
    <Head title="Prépare un nouveau voyage" />

    <div class="max-w-6xl mx-auto px-4 py-10">
        <h1 class="text-3xl md:text-5xl font-semibold tracking-tight text-center mb-10">
            Prépare un nouveau voyage
        </h1>

        <div class="grid md:grid-cols-2 gap-10">

        </div>
    </div>
</template>
