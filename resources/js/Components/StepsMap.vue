<template>
    <div ref="mapElement" class="w-full h-96 rounded shadow" />
</template>

<script setup>
import { onMounted, onBeforeUnmount, ref, watch, nextTick } from 'vue'
import mapboxgl from 'mapbox-gl'

const props = defineProps({
    steps: { type: Array, default: () => [] },
})

const emit = defineEmits(['update:distance'])

const mapElement = ref(null)
let map = null

const TOKEN = import.meta.env.VITE_MAPBOX_KEY
const ROUTE_SRC_ID = 'route-src'
const ROUTE_LAYER_ID = 'route-line'
let markers = []

function haversineDistance([lon1, lat1], [lon2, lat2]) {
    const R = 6371, toRad = d => d * Math.PI / 180
    const dLat = toRad(lat2 - lat1), dLon = toRad(lon2 - lon1)
    const a = Math.sin(dLat/2)**2 + Math.cos(toRad(lat1))*Math.cos(toRad(lat2))*Math.sin(dLon/2)**2
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a))
}
function getTotalDistance(coords) {
    let total = 0
    for (let i = 1; i < coords.length; i++) total += haversineDistance(coords[i-1], coords[i])
    return total
}

function clearRoute() {
    if (!map) return
    if (map.getLayer(ROUTE_LAYER_ID)) map.removeLayer(ROUTE_LAYER_ID)
    if (map.getSource(ROUTE_SRC_ID)) map.removeSource(ROUTE_SRC_ID)
}
function clearMarkers() {
    markers.forEach(m => m.remove())
    markers = []
}

async function renderAll() {
    if (!map) return
    clearRoute()
    clearMarkers()

    const validSteps = props.steps.filter(s => s && s.latitude != null && s.longitude != null)
    const coords = validSteps.map(s => [s.longitude, s.latitude])

    // ✅ Marqueurs numérotés
    validSteps.forEach((step, index) => {
        const el = document.createElement('div')
        el.className = 'step-marker'
        el.textContent = String(index + 1)

        const marker = new mapboxgl.Marker({ element: el })
            .setLngLat([step.longitude, step.latitude])
            .setPopup(new mapboxgl.Popup().setText(step.title || ''))
            .addTo(map)

        markers.push(marker)
    })

    if (coords.length === 0) {
        map.easeTo({ center: [2.35, 48.85], zoom: 4 }) // fallback Europe
        emit('update:distance', '0.0')
        return
    }

    if (coords.length === 1) {
        map.easeTo({ center: coords[0], zoom: 8 })
        emit('update:distance', '0.0')
        return
    }

    const totalDistance = getTotalDistance(coords)
    emit('update:distance', totalDistance.toFixed(1))

    const bounds = new mapboxgl.LngLatBounds()
    coords.forEach(c => bounds.extend(c))
    map.fitBounds(bounds, { padding: 60 })

    if (totalDistance > 5000) {
        // Vol d'oiseau
        map.addSource(ROUTE_SRC_ID, {
            type: 'geojson',
            data: { type: 'Feature', geometry: { type: 'LineString', coordinates: coords } },
        })
        map.addLayer({
            id: ROUTE_LAYER_ID,
            type: 'line',
            source: ROUTE_SRC_ID,
            layout: { 'line-join': 'round', 'line-cap': 'round' },
            paint: { 'line-color': '#f97316', 'line-width': 3, 'line-dasharray': [2, 4] },
        })
        return
    }

    // Directions API
    try {
        const query = coords.map(c => c.join(',')).join(';')
        const url = `https://api.mapbox.com/directions/v5/mapbox/driving/${query}?geometries=geojson&access_token=${TOKEN}`
        const res = await fetch(url)
        const data = await res.json()
        const route = data?.routes?.[0]?.geometry
        if (!route) return

        map.addSource(ROUTE_SRC_ID, { type: 'geojson', data: { type: 'Feature', geometry: route } })
        map.addLayer({
            id: ROUTE_LAYER_ID,
            type: 'line',
            source: ROUTE_SRC_ID,
            layout: { 'line-join': 'round', 'line-cap': 'round' },
            paint: { 'line-color': '#3b82f6', 'line-width': 5 },
        })
    } catch (e) {
        console.error('Erreur Directions API :', e)
    }
}

onMounted(async () => {
    await nextTick()
    mapboxgl.accessToken = TOKEN
    map = new mapboxgl.Map({
        container: mapElement.value,
        style: 'mapbox://styles/mapbox/streets-v12',
        center: [2.35, 48.85],
        zoom: 4,
    })
    map.addControl(new mapboxgl.NavigationControl(), 'top-right')
    map.on('load', renderAll)
})

watch(() => props.steps, () => {
    if (!map) return
    if (!map.isStyleLoaded()) return map.once('load', renderAll)
    renderAll()
}, { deep: true })

onBeforeUnmount(() => {
    clearRoute()
    clearMarkers()
    if (map) { map.remove(); map = null }
})
</script>

<style scoped>
:deep(.step-marker) {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #2563eb;        /* bleu */
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #fff;     /* halo blanc */
    box-shadow: 0 2px 6px rgba(0,0,0,0.3);
    cursor: pointer;
    user-select: none;
}
</style>
