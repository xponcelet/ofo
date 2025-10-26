<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
    steps: { type: Array, default: () => [] },
    activeId: [Number, String],
})
const emit = defineEmits(['update:activeId'])

let map = null
let markers = []
const routeLayerId = 'trip-route'
const routeSourceId = 'trip-route-source'
const mapEl = ref(null)

function toLngLat(s) {
    if (!s) return [NaN, NaN]
    if (s.latitude !== undefined && s.longitude !== undefined) {
        return [Number(s.longitude), Number(s.latitude)]
    }
    if (s.coords) {
        return [Number(s.coords.lng), Number(s.coords.lat)]
    }
    return [NaN, NaN]
}

function drawRoute() {
    if (!map) return
    const coords = props.steps.map(toLngLat).filter(([x, y]) => isFinite(x) && isFinite(y))
    if (!coords.length) return

    if (map.getSource(routeSourceId)) {
        map.getSource(routeSourceId).setData({
            type: 'Feature',
            geometry: { type: 'LineString', coordinates: coords },
        })
    } else {
        map.addSource(routeSourceId, {
            type: 'geojson',
            data: { type: 'Feature', geometry: { type: 'LineString', coordinates: coords } },
        })
    }
    if (!map.getLayer(routeLayerId)) {
        map.addLayer({
            id: routeLayerId,
            type: 'line',
            source: routeSourceId,
            paint: { 'line-color': '#d946ef', 'line-width': 3, 'line-dasharray': [2, 2] },
        })
    }
}

function placeMarkers() {
    if (!map) return
    markers.forEach((m) => m.marker.remove())
    markers = []

    props.steps.forEach((s, i) => {
        const [lng, lat] = toLngLat(s)
        if (!isFinite(lng) || !isFinite(lat)) return

        const el = document.createElement('button')
        el.className =
            'rounded-full shadow ring-1 ring-black/10 bg-white text-gray-900 text-xs font-medium'
        Object.assign(el.style, {
            width: '34px',
            height: '34px',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
        })

        el.textContent = String(s.order ?? s.day ?? i + 1)
        el.addEventListener('click', () => emit('update:activeId', s.id))

        const marker = new window.mapboxgl.Marker({ element: el, anchor: 'center' })
            .setLngLat([lng, lat])
            .addTo(map)

        markers.push({ stepId: s.id, marker, el })
    })

    highlightActive()
}

function highlightActive() {
    markers.forEach(({ stepId, el }) => {
        const active = stepId === props.activeId
        el.style.transform = active ? 'scale(1.08)' : 'scale(1.0)'
        el.style.border = active ? '2px solid #ec4899' : '1px solid rgba(0,0,0,0.1)'
    })

    const activeStep = props.steps.find((s) => s.id === props.activeId)
    if (activeStep && map) {
        const [lng, lat] = toLngLat(activeStep)
        if (isFinite(lng) && isFinite(lat)) {
            map.easeTo({
                center: [lng, lat],
                zoom: Math.max(map.getZoom(), 7),
                duration: 600,
            })
        }
    }
}

function fitAll() {
    const pts = props.steps.map(toLngLat).filter(([x, y]) => isFinite(x) && isFinite(y))
    if (!pts.length) return

    if (pts.length === 1) {
        const [lng, lat] = pts[0]
        map.easeTo({ center: [lng, lat], zoom: 7, duration: 800 })
        return
    }

    const bounds = new window.mapboxgl.LngLatBounds(pts[0], pts[0])
    pts.forEach((p) => bounds.extend(p))

    map.fitBounds(bounds, {
        padding: { top: 40, right: 40, bottom: 40, left: 40 },
        maxZoom: 7,
        duration: 1000,
    })
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

    map.addControl(new window.mapboxgl.NavigationControl({ visualizePitch: true }), 'top-right')

    map.on('load', () => {
        drawRoute()
        placeMarkers()
        fitAll()
    })
})

onBeforeUnmount(() => {
    if (map) map.remove()
})

watch(
    () => props.steps,
    () => {
        if (!map) return
        drawRoute()
        placeMarkers()
        fitAll()
    },
    { deep: true }
)

watch(
    () => props.activeId,
    () => {
        if (!map) return
        highlightActive()
    }
)
</script>

<template>
    <div class="relative w-full h-full">
        <div ref="mapEl" class="w-full h-full"></div>

        <!-- 🎯 Bouton Recentrer -->
        <button
            @click="fitAll"
            class="absolute bottom-4 right-4 z-20 bg-white text-pink-600 border border-gray-200 shadow-md
             hover:bg-pink-50 transition-colors rounded-full p-2 flex items-center justify-center"
            title="Recentrer la carte"
        >
            <span class="material-symbols-rounded text-[22px]">my_location</span>
        </button>
    </div>
</template>

<style scoped>
.material-symbols-rounded {
    font-variation-settings: "FILL" 1, "wght" 400, "GRAD" 0, "opsz" 24;
}
</style>
