<template>
    <Link
        :href="href"
        class="inline-flex items-center px-3 py-1 rounded text-sm font-medium bg-blue-100 text-blue-700 hover:bg-blue-200"
    >
        ➕ {{ label }}
    </Link>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    tripId: { type: [Number, String], required: true },
    // Si fourni => insertion entre deux étapes (après cette étape)
    afterId: { type: [Number, String], default: null },
    // true => insertion au tout début. Ignoré si afterId est fourni.
    atStart: { type: Boolean, default: false },
    label: { type: String, default: 'Ajouter une étape' },
})

const href = computed(() => {
    let url = `/trips/${props.tripId}/steps/create`
    if (props.afterId !== null && props.afterId !== undefined) {
        url += `?after=${props.afterId}`
    } else if (props.atStart) {
        url += `?at_start=1`
    }
    // sinon, pas de paramètre => ajout à la fin (comportement par défaut)
    return url
})
</script>
