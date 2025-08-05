<script setup>
import { onMounted, onBeforeUnmount, watch, ref, computed } from 'vue'
import mapboxgl from 'mapbox-gl'

defineOptions({ name: 'StepMapPreview' })

const props = defineProps({
    steps: {
        type: Array,
        required: true,
    },
    markerColor: {
        type: String,
        default: '#2563eb',
    },
})

const mapContainer = ref(null)
const map = ref(null)
let markers = []

mapboxgl.accessToken = import.meta.env.VITE_MAPBOX_KEY

const stepsWithCoords = computed(() =>
    props.steps.filter(step =>
        Number.isFinite(step.latitude) && Number.isFinite(step.longitude)
    )
)

function initMap() {
    if (!mapContainer.value || !stepsWithCoords.value.length) return

    map.value = new mapboxgl.Map({
        container: mapContainer.value,
        style: 'mapbox://styles/mapbox/streets-v12',
        center: [stepsWithCoords.value[0].longitude, stepsWithCoords.value[0].latitude],
        zoom: 5,
    })

    map.value.on('load', () => {
        addMarkers()
        fitMapToMarkers()
    })
}

function addMarkers() {
    clearMarkers()

    stepsWithCoords.value.forEach((step, index) => {
        // 1. Créer un élément HTML personnalisé pour le marqueur
        const el = document.createElement('div')
        el.className = 'custom-marker'

        // 2. Mettre le numéro d'étape : soit `step.order`, soit l'index
        el.textContent = step.order ?? index + 1

        // 3. Créer le marqueur Mapbox avec l'élément HTML
        const marker = new mapboxgl.Marker({ element: el })
            .setLngLat([step.longitude, step.latitude])
            .setPopup(
                new mapboxgl.Popup({ offset: 25 })
                    .setText(step.location ?? step.title ?? `Étape #${index + 1}`)
            )
            .addTo(map.value)

        markers.push(marker)
    })
}


function clearMarkers() {
    markers.forEach(marker => marker.remove())
    markers = []
}

function fitMapToMarkers() {
    if (stepsWithCoords.value.length < 2) return
    const bounds = new mapboxgl.LngLatBounds()
    stepsWithCoords.value.forEach(step =>
        bounds.extend([step.longitude, step.latitude])
    )
    map.value.fitBounds(bounds, { padding: 50 })
}

onMounted(() => {
    if (stepsWithCoords.value.length) {
        initMap()
    }
})

onBeforeUnmount(() => {
    clearMarkers()
    if (map.value) {
        map.value.remove()
        map.value = null
    }
})
</script>

<template>
    <div class="mt-6 space-y-1">
        <h2 class="text-sm font-medium text-gray-700">Aperçu de la carte</h2>
        <div
            ref="mapContainer"
            class="w-full h-64 rounded-lg border border-gray-200 shadow-sm"
        ></div>
        <p v-if="!stepsWithCoords.length" class="text-xs text-gray-500">
            Aucune étape avec coordonnées valides.
        </p>
    </div>
</template>

<style scoped>
.mapboxgl-control-container {
    display: none;
}

</style>

<style>
.custom-marker {
    background-color: #2563eb;
    color: white;
    width: 28px;
    height: 28px;
    border-radius: 9999px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 600;
    box-shadow: 0 0 0 2px white, 0 2px 4px rgba(0, 0, 0, 0.2);
    cursor: pointer;
}
</style>
