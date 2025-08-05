<template>
  <a
      v-if="hasCoords"
      :href="googleMapsLink"
      class="inline-flex items-center gap-2 px-3 py-2 rounded-md bg-blue-600 text-white text-sm hover:bg-blue-700 transition"
      target="_blank"
      rel="noopener noreferrer"
      :aria-label="`Ouvrir l’itinéraire dans Google Maps vers ${latitude}, ${longitude}`"
  >
    <slot>🗺️ Itinéraire Google Maps</slot>
  </a>

  <span v-else class="text-sm text-gray-400" role="note">
    Coordonnées indisponibles.
  </span>
</template>

<script setup>
import { computed } from 'vue'

defineOptions({ name: 'GoogleMapsNavigationLink' })

const props = defineProps({
  latitude: { type: Number, default: null },
  longitude: { type: Number, default: null },
})

const hasCoords = computed(() =>
    Number.isFinite(props.latitude) && Number.isFinite(props.longitude)
)

const googleMapsLink = computed(() => {
  if (!hasCoords.value) return '#'
  return `https://www.google.com/maps/dir/?api=1&destination=${props.latitude},${props.longitude}`
})
</script>
