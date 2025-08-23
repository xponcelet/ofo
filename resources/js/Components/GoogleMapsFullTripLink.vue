<template>
    <a
        v-if="link"
        :href="link"
        class="inline-flex items-center gap-2 text-blue-600 hover:underline text-sm"
        target="_blank"
        rel="noopener noreferrer"
    >
        🗺️ Itinéraire complet sur Google Maps
    </a>
    <span v-else class="text-sm text-gray-400 italic">Itinéraire indisponible.</span>
</template>

<script setup>
import { computed } from 'vue'

defineOptions({ name: 'GoogleMapsFullTripLink' })

const props = defineProps({
    steps: {
        type: Array,
        required: true
    }
})

const validSteps = computed(() =>
    props.steps.filter(s =>
        Number.isFinite(s.latitude) && Number.isFinite(s.longitude)
    )
)

const link = computed(() => {
    if (validSteps.value.length < 2) return null

    const coords = validSteps.value.map(s => `${s.latitude},${s.longitude}`)

    const origin = coords[0]
    const destination = coords[coords.length - 1]
    const waypoints = coords.slice(1, -1).join('|')

    let url = `https://www.google.com/maps/dir/?api=1&origin=${origin}&destination=${destination}`
    if (waypoints) {
        url += `&waypoints=${encodeURIComponent(waypoints)}`
    }

    return url
})
</script>
