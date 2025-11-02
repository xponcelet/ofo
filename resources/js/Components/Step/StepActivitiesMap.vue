<script setup>
import { onMounted, onBeforeUnmount, ref, watch, nextTick } from 'vue'
import mapboxgl from 'mapbox-gl'

const props = defineProps({
    step: { type: Object, required: true },
    activities: { type: Array, default: () => [] },
    height: { type: String, default: '400px' }, // 🌟 plus grande par défaut
    zoom: { type: Number, default: 6 },
    interactive: { type: Boolean, default: true }, // 🔹 activé par défaut
})

const mapContainer = ref(null)
let map = null
let markers = []

const DEFAULT_CENTER = [10, 50] // 🌍 Europe par défaut

function clearMarkers() {
    markers.forEach(m => m.remove())
    markers = []
}

function addMarker(lng, lat, color = '#e91e63', popupText = null) {
    const marker = new mapboxgl.Marker({ color })
        .setLngLat([lng, lat])
        .addTo(map)

    if (popupText) {
        marker.setPopup(new mapboxgl.Popup({ offset: 25 }).setText(popupText))
    }
    markers.push(marker)
}

function fitToBounds() {
    if (!map || markers.length === 0) return
    const bounds = new mapboxgl.LngLatBounds()
    markers.forEach(m => bounds.extend(m.getLngLat()))
    map.fitBounds(bounds, { padding: 80, duration: 1000 })
}

onMounted(async () => {
    await nextTick()
    mapboxgl.accessToken = import.meta.env.VITE_MAPBOX_KEY

    const stepLng = props.step.longitude
    const stepLat = props.step.latitude
    const hasStepCoords = stepLng != null && stepLat != null

    map = new mapboxgl.Map({
        container: mapContainer.value,
        style: 'mapbox://styles/mapbox/streets-v12',
        center: hasStepCoords ? [stepLng, stepLat] : DEFAULT_CENTER,
        zoom: hasStepCoords ? props.zoom : 4,
        attributionControl: false,
        interactive: props.interactive, // 🔹 activable
    })

    // 🧭 Ajoute les contrôles de navigation
    map.addControl(new mapboxgl.NavigationControl({ showCompass: true }), 'top-right')
    map.addControl(new mapboxgl.FullscreenControl(), 'top-right')

    map.on('load', () => {
        clearMarkers()

        // 🔹 Étape principale
        if (hasStepCoords) {
            addMarker(stepLng, stepLat, '#ec4899', props.step.title || 'Étape')
        }

        // 🔹 Activités
        props.activities.forEach(a => {
            if (a.longitude && a.latitude) {
                addMarker(a.longitude, a.latitude, '#3b82f6', a.title || a.location || 'Activité')
            }
        })

        fitToBounds()
    })
})

watch(
    () => props.activities,
    () => {
        if (!map?.loaded()) return
        clearMarkers()

        if (props.step.latitude && props.step.longitude) {
            addMarker(props.step.longitude, props.step.latitude, '#ec4899', props.step.title)
        }

        props.activities.forEach(a => {
            if (a.longitude && a.latitude) {
                addMarker(a.longitude, a.latitude, '#3b82f6', a.title)
            }
        })

        fitToBounds()
    },
    { deep: true }
)

onBeforeUnmount(() => {
    clearMarkers()
    if (map) map.remove()
})
</script>

<template>
    <div
        ref="mapContainer"
        class="w-full rounded-2xl border border-gray-200 overflow-hidden shadow-sm"
        :style="{ height }"
    />
</template>
