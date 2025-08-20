<!-- resources/js/Components/StepMapPreview.vue -->
<template>
    <div ref="mapEl" class="w-full h-64 rounded overflow-hidden border border-gray-200" />
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch, computed, nextTick } from 'vue'
import mapboxgl from 'mapbox-gl'

const props = defineProps({
    // Cas 1 : un seul point
    latitude:  { type: Number, default: null },
    longitude: { type: Number, default: null },

    // Cas 2 : plusieurs étapes [{ title, latitude, longitude }, ...]
    steps: {
        type: Array,
        default: () => [], // <— évite props.steps === undefined
    },

    // Options
    zoom: { type: Number, default: 5 },
    style: { type: String, default: 'mapbox://styles/mapbox/streets-v12' },
})

// Centre par défaut : Europe
const DEFAULT_CENTER = [10, 50] // [lng, lat]

const mapEl = ref(null)
let map = null
let markers = []

const hasSinglePoint = computed(() => props.latitude !== null && props.longitude !== null)

const points = computed(() => {
    // Si on a des coords directes, on les utilise
    if (hasSinglePoint.value) {
        return [{
            lng: props.longitude,
            lat: props.latitude,
            title: 'Étape',
        }]
    }

    // Sinon, on utilise steps (filtrées)
    // props.steps est TOUJOURS un array grâce au default: () => []
    return props.steps
        .filter(s => s && s.latitude !== null && s.longitude !== null)
        .map(s => ({
            lng: s.longitude,
            lat: s.latitude,
            title: s.title ?? '',
            id: s.id ?? undefined,
        }))
})

function clearMarkers () {
    markers.forEach(m => m.remove())
    markers = []
}

function renderMarkers () {
    if (!map) return
    clearMarkers()

    if (points.value.length === 0) {
        map.setCenter(DEFAULT_CENTER)
        map.setZoom(3.5)
        return
    }

    const bounds = new mapboxgl.LngLatBounds()
    points.value.forEach(p => {
        const el = document.createElement('div')
        el.className = 'rounded-full shadow ring-2 ring-white'
        el.style.width = '14px'
        el.style.height = '14px'
        el.style.background = '#1d4ed8' // bleu (tu peux styliser mieux si tu veux)

        const marker = new mapboxgl.Marker({ element: el })
            .setLngLat([p.lng, p.lat])
            .addTo(map)

        if (p.title) {
            const popup = new mapboxgl.Popup({ closeButton: false, offset: 24 }).setText(p.title)
            marker.setPopup(popup)
        }

        markers.push(marker)
        bounds.extend([p.lng, p.lat])
    })

    if (points.value.length === 1) {
        map.easeTo({ center: [points.value[0].lng, points.value[0].lat], zoom: props.zoom })
    } else {
        map.fitBounds(bounds, { padding: 40, maxZoom: 9 })
    }
}

onMounted(async () => {
    await nextTick()

    map = new mapboxgl.Map({
        container: mapEl.value,
        style: props.style,
        center: DEFAULT_CENTER,
        zoom: 3.5,
        accessToken: import.meta.env.VITE_MAPBOX_KEY,
    })

    map.addControl(new mapboxgl.NavigationControl(), 'top-right')

    map.on('load', () => {
        renderMarkers()
    })
})

// Re-rendre si les points changent (coords ou steps)
watch(points, () => {
    if (!map) return
    // Si la map n’est pas encore prête, attendre le load
    if (!map.isStyleLoaded()) {
        map.once('load', renderMarkers)
    } else {
        renderMarkers()
    }
}, { immediate: false, deep: true })

onBeforeUnmount(() => {
    clearMarkers()
    if (map) {
        map.remove()
        map = null
    }
})
</script>

<style scoped>
/* rien de spécial ici */
</style>
