<script setup>
import { ref, onMounted, watch } from 'vue'
import mapboxgl from 'mapbox-gl'
import MapboxGeocoder from '@mapbox/mapbox-gl-geocoder'

// Props
const props = defineProps({
    modelValue: String,
})

// Émissions
const emit = defineEmits(['update:modelValue', 'update:coords'])

// Références
const inputElement = ref(null)
let geocoder = null

// Clé Mapbox
mapboxgl.accessToken = import.meta.env.VITE_MAPBOX_KEY

onMounted(() => {
    // Initialiser le Geocoder
    geocoder = new MapboxGeocoder({
        accessToken: mapboxgl.accessToken,
        mapboxgl,
        placeholder: 'Cherche un lieu...',
        types: 'place,locality,address',
        marker: false,
    })

    geocoder.addTo(inputElement.value)

    // Lorsqu'un lieu est sélectionné
    geocoder.on('result', (e) => {
        const result = e.result
        emit('update:modelValue', result.place_name)
        emit('update:coords', {
            latitude: result.center[1],
            longitude: result.center[0],
        })
    })

    // Lorsqu'on efface le champ
    geocoder.on('clear', () => {
        emit('update:modelValue', '')
        emit('update:coords', {
            latitude: null,
            longitude: null,
        })
    })
})

// Mise à jour si changement externe
watch(() => props.modelValue, (val) => {
    if (geocoder?.inputEl && geocoder.inputEl.value !== val) {
        geocoder.inputEl.value = val || ''
    }
})
</script>

<template>
    <div ref="inputElement" class="mapbox-autocomplete w-full" />
</template>

<style scoped>
.mapboxgl-ctrl-geocoder {
    min-width: 100%;
}
</style>
