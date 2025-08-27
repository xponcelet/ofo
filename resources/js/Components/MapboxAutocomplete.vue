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

        // Laisser le contrôle se poser puis pré-remplir via l'API officielle
        await nextTick()
        if (props.modelValue) {
            geocoder.setInput(props.modelValue)
        }

        // On n’observe plus le DOM
        observer?.disconnect()
        observer = null
    }
}

onMounted(async () => {
    geocoder = new MapboxGeocoder({
        accessToken: mapboxgl.accessToken,
        mapboxgl,
        placeholder: 'Cherche un lieu...',
        types: 'place,locality,address',
        marker: false,
    })

    geocoder.on('result', (e) => {
        const { place_name, center } = e.result
        emit('update:modelValue', place_name)
        emit('update:coords', { latitude: center[1], longitude: center[0] })
    })

    geocoder.on('clear', () => {
        emit('update:modelValue', '')
        emit('update:coords', { latitude: null, longitude: null })
    })

    // Attendre que le template soit inséré
    await nextTick()

    // 1) Tentative directe
    await attachGeocoder()

    // 2) Si pas encore dans le DOM (swap Inertia / tab...), observer jusqu’à dispo
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

// Refléter les changements externes (pré-remplissage/synchro)
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
    <!-- IMPORTANT : éviter v-if sur ce conteneur ; préférer v-show si besoin -->
    <div ref="inputElement" class="mapbox-autocomplete w-full" />
</template>

<style scoped>
.mapboxgl-ctrl-geocoder {
    min-width: 100%;
}
</style>
