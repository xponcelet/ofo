<script setup>
import { ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue'
import mapboxgl from 'mapbox-gl'
import MapboxGeocoder from '@mapbox/mapbox-gl-geocoder'

const props = defineProps({
    modelValue: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue', 'update:coords'])

const inputElement = ref(null)
let geocoder = null
let attached = false
let observer = null

mapboxgl.accessToken = import.meta.env.VITE_MAPBOX_KEY

async function attachGeocoder() {
    if (!geocoder || attached) return
    if (inputElement.value && document.body.contains(inputElement.value)) {
        geocoder.addTo(inputElement.value)
        attached = true

        await nextTick()
        if (props.modelValue) {
            geocoder.setInput(props.modelValue)
        }

        observer?.disconnect()
        observer = null
    }
}

onMounted(async () => {
    geocoder = new MapboxGeocoder({
        accessToken: mapboxgl.accessToken,
        mapboxgl,
        placeholder: 'Cherche un lieu ou un pays...',
        // 👉 Ici on ajoute "country" et "region"
        types: 'country,region,place,locality,address',
        marker: false,
        language: 'fr',     // optionnel
        limit: 5,           // optionnel
    })

    geocoder.on('result', (e) => {
        const { place_name, center } = e.result
        emit('update:modelValue', place_name)
        emit('update:coords', {
            latitude: center?.[1] ?? null,
            longitude: center?.[0] ?? null,
        })
    })

    geocoder.on('clear', () => {
        emit('update:modelValue', '')
        emit('update:coords', { latitude: null, longitude: null })
    })

    await nextTick()
    await attachGeocoder()

    if (!attached) {
        observer = new MutationObserver(attachGeocoder)
        observer.observe(document.body, { childList: true, subtree: true })
    }
})

onBeforeUnmount(() => {
    observer?.disconnect()
    observer = null
    if (geocoder) {
        try { geocoder.onRemove() } catch (_) {}
        geocoder = null
    }
    attached = false
})

watch(
    () => props.modelValue,
    (val) => {
        if (geocoder) {
            geocoder.setInput(val || '')
        }
    }
)
</script>

<template>
    <div ref="inputElement" class="mapbox-autocomplete w-full" />
</template>

<style scoped>
.mapboxgl-ctrl-geocoder {
    min-width: 100%;
}
</style>
