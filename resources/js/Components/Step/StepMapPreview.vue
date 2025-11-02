<script setup>
import { onMounted, onBeforeUnmount, ref, watch, nextTick } from 'vue'
import mapboxgl from 'mapbox-gl'

const props = defineProps({
    latitude: { type: Number, default: null },
    longitude: { type: Number, default: null },
    zoom: { type: Number, default: 6 },
    height: { type: String, default: '200px' },
})

const mapContainer = ref(null)
let map = null
let marker = null

// 🌍 Europe par défaut
const DEFAULT_CENTER = [10, 50]

onMounted(async () => {
    await nextTick()
    mapboxgl.accessToken = import.meta.env.VITE_MAPBOX_KEY

    // On crée toujours la carte
    map = new mapboxgl.Map({
        container: mapContainer.value,
        style: 'mapbox://styles/mapbox/streets-v12',
        center: props.longitude && props.latitude
            ? [props.longitude, props.latitude]
            : DEFAULT_CENTER,
        zoom: props.longitude && props.latitude ? props.zoom : 3.5,
        attributionControl: false,
        interactive: false,
    })

    // Marker initial (même si coords nulles)
    marker = new mapboxgl.Marker({ color: '#e91e63' })
    if (props.longitude && props.latitude) {
        marker.setLngLat([props.longitude, props.latitude])
    } else {
        marker.setLngLat(DEFAULT_CENTER)
    }
    marker.addTo(map)
})

// 🎯 Met à jour dynamiquement le marqueur et la vue
watch(
    () => [props.latitude, props.longitude],
    ([lat, lng]) => {
        if (!map || lat == null || lng == null) return

        marker.setLngLat([lng, lat])
        map.flyTo({
            center: [lng, lat],
            zoom: props.zoom,
            speed: 1.2,
            essential: true,
        })
    }
)

onBeforeUnmount(() => {
    if (map) map.remove()
})
</script>

<template>
    <div
        ref="mapContainer"
        class="w-full rounded-xl border border-gray-200 overflow-hidden"
        :style="{ height }"
    />
</template>
