<script setup>
import { ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue'
import mapboxgl from 'mapbox-gl'
import MapboxGeocoder from '@mapbox/mapbox-gl-geocoder'

const props = defineProps({
    modelValue: { type: String, default: '' },
})

const emit = defineEmits([
    'update:modelValue',
    'update:coords',
    'update:country',
    'update:countryCode',
])

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
        if (props.modelValue) geocoder.setInput(props.modelValue)
        observer?.disconnect()
        observer = null
    }
}

function cleanLocationName(name, context = []) {
    if (!name) return ''
    // Supprime les parties après la première virgule
    let short = name.split(',')[0].trim()

    // Supprime les répétitions comme "Paris Paris"
    short = short
        .split(' ')
        .filter((v, i, arr) => i === 0 || v.toLowerCase() !== arr[i - 1].toLowerCase())
        .join(' ')

    // Si le lieu correspond aussi à une région/pays du contexte, on garde seulement la 1re partie
    const allContextTexts = context.map(c => c.text.toLowerCase())
    if (allContextTexts.includes(short.toLowerCase())) {
        short = context[0]?.text ?? short
    }

    return short
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

        // 🔹 Nettoyage du nom
        let cleanName = cleanLocationName(e.result.text, context)

        // 🔹 Cas particulier : adresse → tenter de récupérer la localité
        if (place_type.includes('address')) {
            const locality = context.find(c =>
                c.id.startsWith('place') || c.id.startsWith('locality')
            )
            if (locality) cleanName = cleanLocationName(locality.text)
        }

        emit('update:modelValue', cleanName)

        // 🔹 Coordonnées
        emit('update:coords', {
            latitude: center?.[1] ?? null,
            longitude: center?.[0] ?? null,
        })

        // 🔹 Pays + code ISO
        let country = null
        let countryCode = null

        const countryContext = context.find(c => c.id.startsWith('country'))
        if (countryContext) {
            country = countryContext.text
            countryCode = countryContext.short_code?.toUpperCase() ?? null
        }

        // Fallback si le lieu est lui-même un pays
        if (!country && place_type.includes('country')) {
            country = e.result.text
            countryCode = e.result.properties?.short_code?.toUpperCase() ?? null
        }

        emit('update:country', country || '')
        emit('update:countryCode', countryCode || '')
    })

    geocoder.on('clear', () => {
        emit('update:modelValue', '')
        emit('update:coords', { latitude: null, longitude: null })
        emit('update:country', '')
        emit('update:countryCode', '')
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
        if (geocoder) geocoder.setInput(val || '')
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
