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

// 🎨 Couleur selon la catégorie
function markerColorForCategory(cat) {
    const c = (cat || "").toLowerCase()
    if (c.includes("restaurant") || c.includes("food")) return "#ec4899"
    if (c.includes("bar") || c.includes("pub")) return "#f59e0b"
    if (c.includes("hotel") || c.includes("hébergement")) return "#8b5cf6"
    if (c.includes("museum") || c.includes("musée")) return "#3b82f6"
    if (c.includes("park") || c.includes("jardin")) return "#22c55e"
    if (c.includes("attraction") || c.includes("site")) return "#a855f7"
    return "#6b7280"
}

// ✅ Vérifie si le conteneur est visible (évite bug Mapbox sur onglet caché)
function isContainerVisible(el) {
    if (!el) return false
    const rect = el.getBoundingClientRect()
    return rect.width > 0 && rect.height > 0
}

// 🗺️ Initialisation de la carte
async function initMap() {
    await nextTick()
    const el = mapContainer.value
    if (!el || !isContainerVisible(el)) return
    if (!props.step?.latitude || !props.step?.longitude) return

    const { latitude, longitude } = props.step

    // Si la carte existe déjà, on la recentre
    if (map) {
        map.flyTo({ center: [longitude, latitude], zoom: 12 })
        stepMarker?.setLngLat([longitude, latitude])
        updateActivityMarkers()
        fetchPOI()
        return
    }

    try {
        map = new mapboxgl.Map({
            container: el,
            style: "mapbox://styles/mapbox/streets-v12",
            center: [longitude, latitude],
            zoom: 12,
            attributionControl: false,
        })

        // Marqueur principal de l’étape
        stepMarker = new mapboxgl.Marker({ color: "#059669" })
            .setLngLat([longitude, latitude])
            .setPopup(new mapboxgl.Popup().setText(props.step.title || "Étape"))
            .addTo(map)

        map.on("load", () => {
            updateActivityMarkers()
            fetchPOI()
        })

        map.on("error", (e) => console.warn("⚠️ Mapbox error:", e?.error?.message))
        map.on("webglcontextlost", (e) => {
            e.preventDefault()
            console.warn("⚠️ WebGL context lost — reloading map")
            map.remove()
            map = null
            initMap()
        })
    } catch (err) {
        console.error("💥 Erreur d’initialisation Mapbox :", err)
    }
}

// 📍 Affiche les activités de la journée
function updateActivityMarkers() {
    if (!props.showActivities || !map) return

    activityMarkers.forEach((m) => m.remove())
    activityMarkers = []

    props.activities.forEach((a) => {
        if (!a.latitude || !a.longitude) return

        const color = markerColorForCategory(a.category)
        const el = document.createElement("div")
        el.className = "w-4 h-4 rounded-full border border-white shadow"
        el.style.backgroundColor = color

        const popupHtml = `
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
            .setPopup(new mapboxgl.Popup({ offset: 12 }).setHTML(popupHtml))
            .addTo(map)

        activityMarkers.push(marker)
    })
}

// 🧭 Recherche des POI avec Overpass
async function fetchPOI() {
    if (!map || !props.step?.latitude || !props.step?.longitude) return

    poiMarkers.forEach((m) => m.remove())
    poiMarkers = []

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
        const url = `https://overpass-api.de/api/interpreter?data=${encodeURIComponent(query)}`
        const res = await fetch(url)
        const text = await res.text()

        let data
        try {
            data = JSON.parse(text)
        } catch {
            console.warn("⚠️ Overpass a renvoyé une réponse non JSON :", text.slice(0, 100))
            return
        }

        const pois = data.elements || []
        console.log(`✅ ${pois.length} POI trouvés`)

        pois.forEach((poi) => {
            if (!poi.lat || !poi.lon) return

            const el = document.createElement("div")
            el.className =
                "w-2.5 h-2.5 rounded-full bg-gray-400 border border-white opacity-80 cursor-pointer"
            const marker = new mapboxgl.Marker({ element: el })
                .setLngLat([poi.lon, poi.lat])
                .setPopup(new mapboxgl.Popup().setText(poi.tags?.name || "Lieu"))
                .addTo(map)

            poiMarkers.push(marker)
        })
    } catch (e) {
        console.error("Erreur Overpass:", e)
    }
}

// 🧹 Lifecycle
watch(() => props.step, initMap, { deep: true })
watch(() => props.activities, updateActivityMarkers, { deep: true })

onMounted(initMap)
onUnmounted(() => {
    if (map) {
        map.remove()
        map = null
    }
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
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-pink-500"></span>
                Activité
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-gray-400"></span>
                POI à proximité
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
