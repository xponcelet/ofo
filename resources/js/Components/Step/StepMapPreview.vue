<script setup>
import { onMounted, ref, watch } from 'vue'
import mapboxgl from 'mapbox-gl'

const props = defineProps({
    latitude: Number,
    longitude: Number,
    zoom: { type: Number, default: 8 },
    height: { type: String, default: '150px' },
})

const mapContainer = ref(null)
let map = null

onMounted(() => {
    if (!props.latitude || !props.longitude) return

    mapboxgl.accessToken = import.meta.env.VITE_MAPBOX_KEY

    map = new mapboxgl.Map({
        container: mapContainer.value,
        style: 'mapbox://styles/mapbox/streets-v12',
        center: [props.longitude, props.latitude],
        zoom: props.zoom,
        interactive: false,
    })

    new mapboxgl.Marker({ color: '#e91e63' })
        .setLngLat([props.longitude, props.latitude])
        .addTo(map)
})

watch(() => [props.latitude, props.longitude], ([lat, lng]) => {
    if (map && lat && lng) {
        map.setCenter([lng, lat])
    }
})
</script>

<template>
    <div
        ref="mapContainer"
        class="rounded-xl border border-gray-200 overflow-hidden"
        :style="{ height }"
    ></div>
</template>
