<template>
    <div ref="mapEl" class="w-full h-64 rounded overflow-hidden border border-gray-200" />
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue'
import mapboxgl from 'mapbox-gl'

const props = defineProps({
    latitude:  { type: Number, default: null },
    longitude: { type: Number, default: null },
    /** Style Mapbox (par défaut streets-v12) */
    style:     { type: String, default: 'mapbox://styles/mapbox/streets-v12' },
    /** Marqueur draggable ou non */
    interactive: { type: Boolean, default: true },
    /** Recentrer la carte quand latitude/longitude changent (ex: via recherche) */
    recenterOnUpdate: { type: Boolean, default: true },
    /** Calques de labels à écouter (IDs pour streets-v12) */
    labelLayers: {
        type: Array,
        default: () => ['settlement-major-label','settlement-minor-label','place-label'],
    },
    /** Tolérance de clic autour du point (px) */
    clickPaddingPx: { type: Number, default: 10 },
})

const emit = defineEmits(['pick', 'reverseGeocoded'])

const TOKEN = import.meta.env.VITE_MAPBOX_KEY
const mapEl = ref(null)
let map = null
let marker = null

const DEFAULT_CENTER = [10, 50] // [lng, lat]
const DEFAULT_ZOOM   = 4

function getLocalizedName(p) {
    return p?.name_fr || p?.name || p?.text_fr || p?.text || ''
}

function cityPriority(f) {
    const cls = (f.properties?.class || '').toLowerCase()
    const rank = Number.isFinite(+f.properties?.symbolrank) ? +f.properties.symbolrank : 999
    const base =
        cls.includes('city') ? 1 :
            cls.includes('town') ? 2 :
                cls.includes('village') ? 3 : 4
    return base * 100 + rank
}

function setMarker(lng, lat) {
    if (!map) return
    if (!marker) {
        marker = new mapboxgl.Marker({ draggable: props.interactive })
            .setLngLat([lng, lat])
            .addTo(map)
        if (props.interactive) {
            marker.on('dragend', () => {
                const { lng, lat } = marker.getLngLat()
                emit('pick', { latitude: lat, longitude: lng })
            })
        }
    } else {
        marker.setLngLat([lng, lat])
    }
}

onMounted(async () => {
    await nextTick()
    map = new mapboxgl.Map({
        container: mapEl.value,
        style: props.style,
        center: DEFAULT_CENTER,
        zoom: DEFAULT_ZOOM,
        accessToken: TOKEN,
    })
    map.addControl(new mapboxgl.NavigationControl(), 'top-right')

    map.on('load', () => {
        if (props.latitude != null && props.longitude != null) {
            setMarker(props.longitude, props.latitude)
            map.easeTo({ center: [props.longitude, props.latitude], zoom: 9 })
        }
    })

    if (props.interactive) {
        map.on('mousemove', (e) => {
            const available = props.labelLayers.filter(id => map.getLayer(id))
            if (!available.length) { map.getCanvas().style.cursor = ''; return }
            const pad = props.clickPaddingPx
            const bbox = [
                [e.point.x - pad, e.point.y - pad],
                [e.point.x + pad, e.point.y + pad],
            ]
            const feats = map.queryRenderedFeatures(bbox, { layers: available })
            map.getCanvas().style.cursor = feats.length ? 'pointer' : ''
        })

        map.on('click', (e) => {
            const available = props.labelLayers.filter(id => map.getLayer(id))
            if (!available.length) return

            let { lng, lat } = e.lngLat
            const pad = props.clickPaddingPx
            const bbox = [
                [e.point.x - pad, e.point.y - pad],
                [e.point.x + pad, e.point.y + pad],
            ]
            const feats = map.queryRenderedFeatures(bbox, { layers: available })
            if (!feats.length) return

            feats.sort((a, b) => cityPriority(a) - cityPriority(b))
            const f = feats[0]
            const pickedName = getLocalizedName(f.properties || {})

            if (f.geometry?.type === 'Point' && Array.isArray(f.geometry.coordinates)) {
                const [flng, flat] = f.geometry.coordinates
                lng = flng; lat = flat
            }

            setMarker(lng, lat)
            emit('pick', { latitude: lat, longitude: lng })
            if (pickedName) emit('reverseGeocoded', { place: pickedName })
        })
    }
})

watch(
    () => [props.latitude, props.longitude],
    ([lat, lng], [prevLat, prevLng]) => {
        if (!map) return

        if (lat == null || lng == null) {
            map.easeTo({ center: DEFAULT_CENTER, zoom: DEFAULT_ZOOM })
            if (marker) { marker.remove(); marker = null }
            return
        }

        setMarker(lng, lat)

        const changed =
            prevLat !== undefined && prevLng !== undefined &&
            (lat !== prevLat || lng !== prevLng)

        if (changed && props.recenterOnUpdate) {
            map.easeTo({ center: [lng, lat], zoom: 9 })
        }
    }
)

onBeforeUnmount(() => {
    if (marker) { marker.remove(); marker = null }
    if (map) { map.remove(); map = null }
})
</script>
