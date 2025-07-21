<script setup>
import { onMounted, watch, ref } from 'vue'
import mapboxgl from 'mapbox-gl'

const props = defineProps({
    latitude: Number,
    longitude: Number,
})

const mapContainer = ref(null)
const map = ref(null)
const marker = ref(null)

mapboxgl.accessToken = import.meta.env.VITE_MAPBOX_KEY

function initMap() {
    const defaultCenter = [10, 50] // Europe
    const center = props.latitude && props.longitude ? [props.longitude, props.latitude] : defaultCenter

    map.value = new mapboxgl.Map({
        container: mapContainer.value,
        style: 'mapbox://styles/mapbox/streets-v12',
        center,
        zoom: 5,
    })

    if (props.latitude && props.longitude) {
        marker.value = new mapboxgl.Marker({ color: '#2563eb' }) // bleu Tailwind
            .setLngLat([props.longitude, props.latitude])
            .addTo(map.value)
    }
}

function updateMap() {
    if (!map.value || !props.latitude || !props.longitude) return

    const coords = [props.longitude, props.latitude]
    map.value.flyTo({ center: coords, zoom: 6 })

    if (marker.value) {
        marker.value.setLngLat(coords)
    } else {
        marker.value = new mapboxgl.Marker({ color: '#2563eb' })
            .setLngLat(coords)
            .addTo(map.value)
    }
}

onMounted(initMap)
watch(() => [props.latitude, props.longitude], updateMap)
</script>

<template>
    <div class="mt-6 space-y-1">
        <h2 class="text-sm font-medium text-gray-700">Aperçu de la carte</h2>
        <div
            ref="mapContainer"
            class="w-full h-64 rounded-lg border border-gray-200 shadow-sm"
        ></div>
    </div>
</template>

<style scoped>
.mapboxgl-control-container {
    display: none;
}
</style>
