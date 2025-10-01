<script setup>
import { ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue'
import mapboxgl from 'mapbox-gl'
import MapboxGeocoder from '@mapbox/mapbox-gl-geocoder'

const props = defineProps({
    modelValue: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue', 'update:coords', 'update:country'])

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
        types: 'country,region,place,locality,address',
        marker: false,
        language: 'fr',
        limit: 5,
    })

    geocoder.on('result', (e) => {
        const { place_name, center, context = [], place_type = [] } = e.result

        // Nom affiché
        emit('update:modelValue', place_name)

        // Coordonnées
        emit('update:coords', {
            latitude: center?.[1] ?? null,
            longitude: center?.[0] ?? null,
        })

        // Pays
        let country = null

        // Cherche dans context (ex: [ { id: 'country.123', text: 'France' } ])
        if (context.length) {
            const countryContext = context.find((c) => c.id.startsWith('country'))
            if (countryContext) {
                country = countryContext.text
            }
        }

        // Fallback : si l’objet lui-même est un pays
        if (!country && place_type.includes('country')) {
            country = e.result.text
        }

        emit('update:country', country || '')
    })

    geocoder.on('clear', () => {
        emit('update:modelValue', '')
        emit('update:coords', { latitude: null, longitude: null })
        emit('update:country', '')
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
        try {
            geocoder.onRemove()
        } catch (_) {}
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
