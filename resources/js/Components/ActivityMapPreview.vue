<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
    step: { type: Object, required: true },
    activities: { type: Array, default: () => [] },
})

let map = null
let markers = []
const mapEl = ref(null)

function toLngLat(obj) {
    if (!obj) return [NaN, NaN]
    if (obj.longitude !== undefined && obj.latitude !== undefined)
        return [Number(obj.longitude), Number(obj.latitude)]
    return [NaN, NaN]
}

function clearMarkers() {
    markers.forEach((m) => m.remove())
    markers = []
}

function placeMarkers() {
    if (!map) return
    clearMarkers()

    // 📍 Étape principale (lieu du jour)
    const [lng, lat] = toLngLat(props.step)
    if (isFinite(lng) && isFinite(lat)) {
        const el = document.createElement('div')
        el.className =
            'w-4 h-4 bg-pink-600 rounded-full border-2 border-white shadow'
        const marker = new window.mapboxgl.Marker({ element: el })
            .setLngLat([lng, lat])
            .setPopup(new window.mapboxgl.Popup().setText(props.step.location || 'Étape'))
            .addTo(map)
        markers.push(marker)
    }

    // 🎯 Activités du jour
    props.activities.forEach((a) => {
        const [lng, lat] = toLngLat(a)
        if (!isFinite(lng) || !isFinite(lat)) return
        const el = document.createElement('div')
        el.className =
            'w-3 h-3 bg-emerald-600 rounded-full border border-white shadow'
        const marker = new window.mapboxgl.Marker({ element: el })
            .setLngLat([lng, lat])
            .setPopup(new window.mapboxgl.Popup().setText(a.title || 'Activité'))
            .addTo(map)
        markers.push(marker)
    })

    fitToAll()
}

function fitToAll() {
    if (!map) return

    const allCoords = []

    // Ajoute la localisation de l’étape
    const stepCoords = toLngLat(props.step)
    if (isFinite(stepCoords[0]) && isFinite(stepCoords[1])) allCoords.push(stepCoords)

    // Ajoute celles des activités
    props.activities.forEach((a) => {
        const coords = toLngLat(a)
        if (isFinite(coords[0]) && isFinite(coords[1])) allCoords.push(coords)
    })

    if (allCoords.length === 0) return

    if (allCoords.length === 1) {
        map.easeTo({ center: allCoords[0], zoom: 10 })
    } else {
        const bounds = new window.mapboxgl.LngLatBounds()
        allCoords.forEach((c) => bounds.extend(c))
        map.fitBounds(bounds, { padding: 60, maxZoom: 12 })
    }
}

onMounted(() => {
    if (!window.mapboxgl?.Map) return
    window.mapboxgl.accessToken = import.meta.env.VITE_MAPBOX_KEY

    map = new window.mapboxgl.Map({
        container: mapEl.value,
        style: 'mapbox://styles/mapbox/streets-v12',
        center: [2, 48],
        zoom: 3.5,
    })

    map.addControl(new window.mapboxgl.NavigationControl(), 'top-right')

    map.on('load', () => {
        placeMarkers()
    })
})

onBeforeUnmount(() => {
    if (map) map.remove()
})

watch(
    () => [props.step, props.activities],
    () => {
        if (!map) return
        placeMarkers()
    },
    { deep: true }
)
</script>

<template>
    <div ref="mapEl" class="w-full h-72 rounded-xl overflow-hidden border border-gray-200 shadow-sm" />
</template>
