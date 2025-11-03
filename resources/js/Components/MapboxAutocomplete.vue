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
    'select-place', // ✅ nouvel événement
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
    let short = name.split(',')[0].trim()
    short = short
        .split(' ')
        .filter((v, i, arr) => i === 0 || v.toLowerCase() !== arr[i - 1].toLowerCase())
        .join(' ')
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
        placeholder: 'Rechercher une destination...',
        types: 'country,region,place,locality,address',
        marker: false,
        language: 'fr',
        limit: 5,
    })

    geocoder.on('result', (e) => {
        const { place_name, center, context = [], place_type = [] } = e.result
        let cleanName = cleanLocationName(e.result.text, context)

        if (place_type.includes('address')) {
            const locality = context.find(c =>
                c.id.startsWith('place') || c.id.startsWith('locality')
            )
            if (locality) cleanName = cleanLocationName(locality.text)
        }

        emit('update:modelValue', cleanName)
        emit('update:coords', {
            latitude: center?.[1] ?? null,
            longitude: center?.[0] ?? null,
        })

        let country = null
        let countryCode = null
        const countryContext = context.find(c => c.id.startsWith('country'))
        if (countryContext) {
            country = countryContext.text
            countryCode = countryContext.short_code?.toUpperCase() ?? null
        }

        if (!country && place_type.includes('country')) {
            country = e.result.text
            countryCode = e.result.properties?.short_code?.toUpperCase() ?? null
        }

        emit('update:country', country || '')
        emit('update:countryCode', countryCode || '')

        // ✅ Nouvel événement complet pour faciliter l’intégration (comme dans ActivityModal)
        emit('select-place', {
            name: cleanName,
            address: place_name,
            latitude: center?.[1] ?? null,
            longitude: center?.[0] ?? null,
            country: country || '',
            country_code: countryCode || '',
        })
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
:deep(.mapboxgl-ctrl-geocoder--input) {
    border-radius: 12px;
    padding: 8px 12px 8px 36px;
    font-size: 15px;
    background-color: #fff;
    color: #1f1f1f;
    border: 1px solid #ccc;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
    transition: all 0.2s ease;
    height: 42px;
    line-height: 1.4;
}

:deep(.mapboxgl-ctrl-geocoder--input:focus) {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}

:deep(.mapboxgl-ctrl-geocoder--icon) {
    fill: #3b82f6;
    width: 18px;
    height: 18px;
    top: 50%;
    transform: translateY(-50%);
    left: 10px;
    position: absolute;
}
</style>
