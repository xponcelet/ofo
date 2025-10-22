<script setup>
import { onMounted, ref, onBeforeUnmount, watch } from "vue"
import mapboxgl from "mapbox-gl"

mapboxgl.accessToken = import.meta.env.VITE_MAPBOX_KEY

const props = defineProps({
    latitude: { type: Number, required: false, default: null },
    longitude: { type: Number, required: false, default: null },
})

const mapContainer = ref(null)
let map = null

onMounted(() => {
    if (!props.latitude || !props.longitude) return

    map = new mapboxgl.Map({
        container: mapContainer.value,
        style: "mapbox://styles/mapbox/light-v11",
        center: [props.longitude, props.latitude],
        zoom: 4.5,
        interactive: false,
    })

    // Marqueur discret
    new mapboxgl.Marker({ color: "#2563eb", scale: 0.8 })
        .setLngLat([props.longitude, props.latitude])
        .addTo(map)
})

onBeforeUnmount(() => {
    if (map) map.remove()
})

watch(
    () => [props.latitude, props.longitude],
    ([lat, lon]) => {
        if (map && lat && lon) map.setCenter([lon, lat])
    }
)
</script>

<template>
    <div ref="mapContainer" class="w-full h-40 rounded-xl overflow-hidden"></div>
</template>

<style scoped>
.mapboxgl-control-container {
    display: none !important; /* enlève logo & boutons */
}
</style>
