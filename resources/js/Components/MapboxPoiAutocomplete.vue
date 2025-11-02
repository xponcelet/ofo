<script setup>
import { ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue'
import mapboxgl from 'mapbox-gl'
import MapboxGeocoder from '@mapbox/mapbox-gl-geocoder'

const props = defineProps({
    modelValue: { type: String, default: '' },
    placeholder: { type: String, default: 'Rechercher un lieu (ex: Tour Eiffel, musée, café...)' },
    proximity: { type: Object, default: null }, // { latitude, longitude }
})

const emit = defineEmits([
    'update:modelValue',
    'update:coords',
    'select-place',
])

const inputEl = ref(null)
let geocoder = null
let attached = false
let observer = null

mapboxgl.accessToken = import.meta.env.VITE_MAPBOX_KEY

async function attachGeocoder() {
    if (!geocoder || attached) return
    if (inputEl.value && document.body.contains(inputEl.value)) {
        geocoder.addTo(inputEl.value)
        attached = true
        await nextTick()
        if (props.modelValue) geocoder.setInput(props.modelValue)
        observer?.disconnect()
        observer = null
    }
}

onMounted(async () => {
    geocoder = new MapboxGeocoder({
        accessToken: mapboxgl.accessToken,
        mapboxgl,
        placeholder: props.placeholder,
        types: 'poi,place,address,locality,neighborhood',
        limit: 10,
        marker: false,
        language: 'fr',
        proximity: props.proximity
            ? { longitude: props.proximity.longitude, latitude: props.proximity.latitude }
            : undefined,
    })

    geocoder.on('result', (e) => {
        const { text, place_name, center, properties } = e.result
        const [lng, lat] = center
        emit('update:modelValue', text)
        emit('update:coords', { latitude: lat, longitude: lng })
        emit('select-place', {
            name: text,
            address: place_name,
            latitude: lat,
            longitude: lng,
            category: properties?.category || '',
        })
    })

    // ✅ Fallback global si aucun résultat local
    geocoder.on('results', (e) => {
        if (!e.features?.length) {
            geocoder.setProximity(null)
            geocoder.query(geocoder._inputEl?.value || '')
        }
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

// ✅ Correction du watcher qui causait l’erreur
watch(
    () => props.modelValue,
    (val) => {
        if (geocoder && typeof geocoder.setInput === 'function') {
            geocoder.setInput(val || '')
        }
    }
)
</script>

<template>
    <div ref="inputEl" class="mapbox-poi-autocomplete w-full" />
</template>

<style scoped>
:deep(.mapboxgl-ctrl-geocoder) {
    width: 100%;
    max-width: 100%;
}

:deep(.mapboxgl-ctrl-geocoder--input) {
    border-radius: 12px;
    padding: 8px 12px 8px 36px;
    font-size: 15px;
    background-color: #fff;
    color: #1f1f1f;
    border: 2px solid #f9a8d4; /* rose clair */
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    transition: all 0.2s ease;
    height: 42px;
    line-height: 1.4;
}

:deep(.mapboxgl-ctrl-geocoder--input:focus) {
    outline: none;
    border-color: #ec4899;
    box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.2);
}

:deep(.mapboxgl-ctrl-geocoder--icon) {
    fill: #ec4899;
    width: 18px;
    height: 18px;
    top: 50%;
    transform: translateY(-50%);
    left: 10px;
    position: absolute;
}
</style>
