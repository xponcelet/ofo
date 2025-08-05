<script setup>
import { onMounted, onBeforeUnmount, watch, ref, computed } from 'vue'
import mapboxgl from 'mapbox-gl'

defineOptions({ name: 'StepMapPreview' })

const props = defineProps({
    latitude: { type: Number, default: null },
    longitude: { type: Number, default: null },
    initialZoom: { type: Number, default: 5 },
    markerColor: { type: String, default: '#2563eb' }, // bleu Tailwind
    hideControls: { type: Boolean, default: true },     // pour garder ton UI minimaliste
})

const mapContainer = ref(null)
const map = ref(null)
const marker = ref(null)
let resizeObserver = null

mapboxgl.accessToken = import.meta.env.VITE_MAPBOX_KEY

const hasCoords = computed(() =>
    Number.isFinite(props.latitude) && Number.isFinite(props.longitude)
)

const defaultCenter = [10, 50] // Europe
const currentCenter = computed(() =>
    hasCoords.value ? [props.longitude, props.latitude] : defaultCenter
)

function initMap() {
    if (!mapContainer.value) return
    if (!mapboxgl.accessToken) {
        console.warn('[StepMapPreview] VITE_MAPBOX_KEY est manquant.')
    }

    map.value = new mapboxgl.Map({
        container: mapContainer.value,
        style: 'mapbox://styles/mapbox/streets-v12',
        center: currentCenter.value,
        zoom: props.initialZoom,
    })

    map.value.on('load', () => {
        if (hasCoords.value) {
            marker.value = new mapboxgl.Marker({ color: props.markerColor })
                .setLngLat([props.longitude, props.latitude])
                .addTo(map.value)
        }
    })

    // Resize auto (onglets, conteneur qui change de taille, etc.)
    resizeObserver = new ResizeObserver(() => {
        map.value?.resize()
    })
    resizeObserver.observe(mapContainer.value)
}

function updateMap() {
    if (!map.value || !hasCoords.value) return
    const coords = [props.longitude, props.latitude]

    // Déplace la caméra
    map.value.flyTo({ center: coords, zoom: Math.max(props.initialZoom, 6) })

    // Met à jour ou crée le marqueur
    if (marker.value) {
        marker.value.setLngLat(coords)
    } else {
        marker.value = new mapboxgl.Marker({ color: props.markerColor })
            .setLngLat(coords)
            .addTo(map.value)
    }
}

onMounted(initMap)

watch(
    () => [props.latitude, props.longitude],
    () => {
        if (hasCoords.value) updateMap()
    }
)

onBeforeUnmount(() => {
    try {
        if (resizeObserver && mapContainer.value) {
            resizeObserver.unobserve(mapContainer.value)
            resizeObserver = null
        }
        marker.value = null
        if (map.value) {
            map.value.remove()
            map.value = null
        }
    } catch (e) {
        console.warn('[StepMapPreview] cleanup error', e)
    }
})
</script>

<template>
    <div class="mt-6 space-y-1">
        <h2 class="text-sm font-medium text-gray-700">Aperçu de la carte</h2>
        <div
            ref="mapContainer"
            class="w-full h-64 rounded-lg border border-gray-200 shadow-sm"
        ></div>
        <p v-if="!hasCoords" class="text-xs text-gray-500">
            Aucune coordonnée valide — affichage centré sur l’Europe.
        </p>
    </div>
</template>

<style scoped>
/* Option pour masquer les contrôles Mapbox si hideControls = true
   (si tu veux conditionner finement, tu peux ajouter une classe dynamiquement) */
.mapboxgl-control-container {
    display: v-bind('hideControls ? "none" : "block"');
}
</style>
