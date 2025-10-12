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
        default: () => [],
    },

    // Options
    zoom: { type: Number, default: 5 },
    style: { type: String, default: 'mapbox://styles/mapbox/streets-v12' },

    // 🔹 Nouveaux props pour les POI
    showPoi: { type: Boolean, default: false },     // afficher ou non les POI
    poiQuery: { type: String, default: 'restaurant' }, // type de POI à chercher
    poiLimit: { type: Number, default: 10 },        // nombre maximum de POI à afficher
})

// Centre par défaut : Europe
const DEFAULT_CENTER = [10, 50]

const mapEl = ref(null)
let map = null
let markers = []
let poiLayer = null // 🔹 référence pour la couche POI

const hasSinglePoint = computed(() => props.latitude !== null && props.longitude !== null)

const points = computed(() => {
    if (hasSinglePoint.value) {
        return [{
            lng: props.longitude,
            lat: props.latitude,
            title: 'Étape',
        }]
    }

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
        el.style.background = '#1d4ed8' // bleu

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

    // 🔹 Si POI activés, on (re)charge la couche
    if (props.showPoi) {
        renderPoiLayer()
    }
}

async function renderPoiLayer() {
    if (!map) return

    // Supprime la couche précédente s’il y en a une
    if (poiLayer) {
        poiLayer.remove()
        poiLayer = null
    }

    // Déterminer la position centrale (premier point ou coordonnées)
    const center = hasSinglePoint.value
        ? [props.longitude, props.latitude]
        : (points.value.length ? [points.value[0].lng, points.value[0].lat] : DEFAULT_CENTER)

    // Charger le composant PoiMapLayer dynamiquement
    const { default: PoiMapLayerClass } = await import('./PoiMapLayer.js')
    poiLayer = new PoiMapLayerClass(map, center, props.poiQuery, props.poiLimit)
    poiLayer.load()
}

onMounted(async () => {
    await nextTick()

    mapboxgl.accessToken = import.meta.env.VITE_MAPBOX_KEY
    map = new mapboxgl.Map({
        container: mapEl.value,
        style: props.style,
        center: DEFAULT_CENTER,
        zoom: 3.5,
    })

    map.addControl(new mapboxgl.NavigationControl(), 'top-right')
    map.on('load', renderMarkers)
})

// Recharger marqueurs et POI si les étapes changent
watch(points, () => {
    if (!map) return
    if (!map.isStyleLoaded()) {
        map.once('load', renderMarkers)
    } else {
        renderMarkers()
    }
}, { deep: true })

// 🔹 Mettre à jour les POI si le type change (ex: restaurant → hotel)
watch(() => props.poiQuery, () => {
    if (props.showPoi) renderPoiLayer()
})

onBeforeUnmount(() => {
    clearMarkers()
    if (poiLayer) poiLayer.remove()
    if (map) map.remove()
})
</script>
