<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from "vue"
import mapboxgl from "mapbox-gl"

mapboxgl.accessToken = import.meta.env.VITE_MAPBOX_KEY

const props = defineProps({
    step: { type: Object, required: true },
    activities: { type: Array, default: () => [] },
    showActivities: { type: Boolean, default: false },
})

const mapContainer = ref(null)
let map = null
let stepMarker = null
let activityMarkers = []
let poiMarkers = []
const showPOI = ref(false) // ✅ Toggle

// 🎨 Couleurs par jour
const dayColors = [
    "#ef4444", "#f97316", "#eab308", "#22c55e", "#3b82f6", "#a855f7", "#ec4899"
]

// 🎯 Couleur d’une activité
function getColorForActivity(activity, index) {
    return dayColors[activity.day_index !== undefined
        ? activity.day_index % dayColors.length
        : index % dayColors.length]
}

// ✅ Vérifie si la carte est visible
function isContainerVisible(el) {
    if (!el) return false
    const rect = el.getBoundingClientRect()
    return rect.width > 0 && rect.height > 0
}

// 🗺️ Initialisation
async function initMap() {
    await nextTick()
    const el = mapContainer.value
    if (!el || !isContainerVisible(el)) return
    if (!props.step?.latitude || !props.step?.longitude) return

    const { latitude, longitude } = props.step

    if (map) {
        updateStepMarker()
        updateActivityMarkers()
        return
    }

    map = new mapboxgl.Map({
        container: el,
        style: "mapbox://styles/mapbox/streets-v12",
        center: [longitude, latitude],
        zoom: 11,
        attributionControl: false,
    })

    map.on("load", () => {
        updateStepMarker()
        updateActivityMarkers()
    })
}

// 📍 Étape principale
function updateStepMarker() {
    if (!map || !props.step?.latitude || !props.step?.longitude) return
    if (stepMarker) stepMarker.remove()

    stepMarker = new mapboxgl.Marker({ color: "#059669" })
        .setLngLat([props.step.longitude, props.step.latitude])
        .setPopup(new mapboxgl.Popup().setText(props.step.title || "Étape"))
        .addTo(map)

    map.flyTo({ center: [props.step.longitude, props.step.latitude], zoom: 11 })
}

// 🎯 Activités
function updateActivityMarkers() {
    if (!map) return
    console.log("📍 Activities reçues :", props.activities)

    activityMarkers.forEach((m) => m.remove())
    activityMarkers = []

    if (!props.showActivities) return

    props.activities.forEach((a, i) => {
        if (!a.latitude || !a.longitude) return

        const color = getColorForActivity(a, i)
        const el = document.createElement("div")
        el.className = "w-4 h-4 rounded-full border border-white shadow cursor-pointer"
        el.style.backgroundColor = color

        const html = `
            <div class='text-sm font-semibold text-gray-800 mb-1'>
                ${a.title || "Activité"}
            </div>
            ${a.category ? `<div class='text-xs text-gray-500 mb-1'>${a.category}</div>` : ""}
            ${
            a.latitude && a.longitude
                ? `<a href="https://www.google.com/maps/dir/?api=1&destination=${a.latitude},${a.longitude}"
                        target="_blank"
                        class="text-xs text-pink-600 hover:underline">📍 Itinéraire</a>`
                : ""
        }
        `

        const marker = new mapboxgl.Marker({ element: el })
            .setLngLat([a.longitude, a.latitude])
            .setPopup(new mapboxgl.Popup({ offset: 12 }).setHTML(html))
            .addTo(map)

        activityMarkers.push(marker)
    })
}

// 🧭 Lieux à proximité (POI)
async function fetchPOI() {
    if (!map || !props.step?.latitude || !props.step?.longitude) return

    poiMarkers.forEach((m) => m.remove())
    poiMarkers = []
    if (!showPOI.value) return

    const { latitude, longitude } = props.step
    const query = `
      [out:json][timeout:25];
      (
        node["tourism"="attraction"](around:1000,${latitude},${longitude});
        node["amenity"="restaurant"](around:1000,${latitude},${longitude});
        node["leisure"="park"](around:1000,${latitude},${longitude});
      );
      out center;
    `

    try {
        const res = await fetch(
            `https://overpass-api.de/api/interpreter?data=${encodeURIComponent(query)}`
        )
        const json = await res.json()
        const pois = json.elements || []
        console.log(`✅ ${pois.length} lieux trouvés`)

        pois.forEach((poi) => {
            if (!poi.lat || !poi.lon) return
            const el = document.createElement("div")
            el.className = "w-2.5 h-2.5 rounded-full bg-gray-400 border border-white opacity-80 cursor-pointer"
            const marker = new mapboxgl.Marker({ element: el })
                .setLngLat([poi.lon, poi.lat])
                .setPopup(new mapboxgl.Popup().setText(poi.tags?.name || "Lieu"))
                .addTo(map)
            poiMarkers.push(marker)
        })
    } catch (e) {
        console.error("Erreur overpass:", e)
    }
}

// 🔄 Watchers
watch(() => props.step, () => {
    updateStepMarker()
    updateActivityMarkers()
    fetchPOI()
}, { deep: true })

watch(() => props.activities, updateActivityMarkers, { deep: true })

watch(showPOI, fetchPOI)

onMounted(initMap)
onUnmounted(() => {
    map?.remove()
    map = null
})
</script>

<template>
    <div class="h-72 w-full rounded-xl overflow-hidden relative">
        <div ref="mapContainer" class="w-full h-full"></div>

        <!-- 🌍 Légende -->
        <div
            class="absolute bottom-3 left-3 bg-white/90 backdrop-blur-md rounded-xl shadow px-3 py-2 text-xs text-gray-700 space-y-1"
        >
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-emerald-600"></span>
                Étape
            </div>
            <div v-for="(color, i) in dayColors" :key="i" class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full" :style="{ backgroundColor: color }"></span>
                Jour {{ i + 1 }}
            </div>
            <div class="pt-2 border-t border-gray-200 mt-1">
                <button
                    @click="showPOI = !showPOI"
                    class="text-xs px-3 py-1 rounded-full border border-gray-300 bg-white hover:bg-gray-100 transition"
                >
                    {{ showPOI ? "Masquer les lieux à proximité" : "Afficher les lieux à proximité" }}
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.mapboxgl-popup-content {
    border-radius: 10px !important;
    padding: 8px 10px !important;
}
</style>
