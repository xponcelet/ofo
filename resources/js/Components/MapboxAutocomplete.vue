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

function attachGeocoder() {
    if (!geocoder || attached) return
    if (inputElement.value && document.body.contains(inputElement.value)) {
        geocoder.addTo(inputElement.value)
        attached = true

        // Pré-remplir si une valeur est déjà fournie par le parent
        if (props.modelValue && geocoder.inputEl) {
            geocoder.inputEl.value = props.modelValue
        }
        // On n’a plus besoin d’observer
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

    // Attendre que le template soit réellement inséré
    await nextTick()

    // 1) Tentative directe
    attachGeocoder()

    // 2) Si pas encore dans le DOM (Inertia swap, tab hidden, etc.), on observe le DOM jusqu’à dispo
    if (!attached) {
        observer = new MutationObserver(attachGeocoder)
        observer.observe(document.body, { childList: true, subtree: true })
    }
})

onBeforeUnmount(() => {
    observer?.disconnect()
    observer = null
    if (geocoder) {
        try {
            // Évite que Mapbox garde des handlers sur un conteneur disparu
            geocoder.onRemove()
        } catch (_) {}
        geocoder = null
    }
    attached = false
})

// Si la valeur externe change, on reflète dans le champ (sans déclencher d’events)
watch(
    () => props.modelValue,
    (val) => {
        if (geocoder?.inputEl && geocoder.inputEl.value !== (val || '')) {
            geocoder.inputEl.value = val || ''
        }
    }
)
</script>

<template>
    <!-- IMPORTANT : laisser ce div exister et rester dans le DOM (éviter v-if sur le parent) -->
    <div ref="inputElement" class="mapbox-autocomplete w-full" />
</template>

<style scoped>
.mapboxgl-ctrl-geocoder {
    min-width: 100%;
}
</style>
