<script setup>
import { ref, watch, onMounted, nextTick } from 'vue'
import mapboxgl from 'mapbox-gl'

const props = defineProps({
    latitude: { type: Number, required: true },
    longitude: { type: Number, required: true },
})

const emit = defineEmits(['select-poi'])

const mapEl = ref(null)
let map = null
const pois = ref([])
const markers = ref([])
const selectedType = ref('restaurant')
const isLoading = ref(false)

const poiTypes = [
    { key: 'restaurant', icon: 'restaurant', label: 'Restaurants' },
    { key: 'hotel', icon: 'hotel', label: 'Hôtels' },
    { key: 'bar', icon: 'local_bar', label: 'Bars' },
    { key: 'museum', icon: 'museum', label: 'Musées' },
    { key: 'park', icon: 'park', label: 'Parcs' },
    { key: 'attraction', icon: 'star', label: 'Attractions' },
]

// 🔄 Nettoyage des anciens marqueurs
function clearMarkers() {
    markers.value.forEach((m) => m.remove())
    markers.value = []
}

// 🔍 Recherche de POI via Overpass
async function fetchPOI() {
    if (!map) return
    isLoading.value = true
    clearMarkers()

    try {
        const radius = 1000 // m

        const osmKey = {
            restaurant: 'amenity=restaurant',
            hotel: 'tourism=hotel',
            bar: 'amenity=bar',
            museum: 'tourism=museum',
            park: 'leisure=park',
            attraction: 'tourism=attraction',
        }[selectedType.value] || 'amenity=restaurant'

        const query = `
        [out:json][timeout:25];
        (
            node[${osmKey}](around:${radius},${props.latitude},${props.longitude});
            way[${osmKey}](around:${radius},${props.latitude},${props.longitude});
            relation[${osmKey}](around:${radius},${props.latitude},${props.longitude});
        );
        out center;
        `
        const url = `https://overpass-api.de/api/interpreter?data=${encodeURIComponent(query)}`

        const res = await fetch(url)
        const data = await res.json()

        pois.value = (data.elements || []).map((el) => {
            const lat = el.lat ?? el.center?.lat
            const lon = el.lon ?? el.center?.lon
            const tags = el.tags || {}
            return {
                id: el.id,
                lat,
                lon,
                name: tags.name || '(Lieu sans nom)',
                category: tags.amenity || tags.tourism || tags.leisure || '',
                address: [tags['addr:housenumber'], tags['addr:street'], tags['addr:city']]
                    .filter(Boolean)
                    .join(' ') || tags['addr:full'] || '',
                tags,
            }
        })

        pois.value.forEach((poi) => {
            const marker = new mapboxgl.Marker({ color: '#ec4899' })
                .setLngLat([poi.lon, poi.lat])
                .setPopup(new mapboxgl.Popup().setText(poi.name))
                .addTo(map)
            markers.value.push(marker)
        })
    } catch (err) {
        console.error('Erreur Overpass', err)
    } finally {
        isLoading.value = false
    }
}

// Initialisation
onMounted(async () => {
    await nextTick()
    mapboxgl.accessToken = import.meta.env.VITE_MAPBOX_KEY

    const lng = props.longitude ?? 2.35
    const lat = props.latitude ?? 48.85

    map = new mapboxgl.Map({
        container: mapEl.value,
        style: 'mapbox://styles/mapbox/streets-v12',
        center: [lng, lat],
        zoom: 14,
    })

    map.addControl(new mapboxgl.NavigationControl(), 'top-right')

    map.on('load', () => {
        map.resize()
        fetchPOI()
    })
})

watch(selectedType, fetchPOI)
watch(() => props.latitude, () => map && map.resize())
</script>

<template>
    <div class="grid grid-cols-1 lg:grid-cols-[2fr_1fr] gap-5">
        <!-- Carte -->
        <div class="relative min-h-[400px] rounded-xl overflow-hidden border border-gray-200 bg-gray-50">
            <!-- Type de POI -->
            <div class="absolute top-3 left-3 z-10 flex gap-2 bg-white/80 backdrop-blur-sm rounded-lg px-3 py-2 shadow">
                <button
                    v-for="type in poiTypes"
                    :key="type.key"
                    @click="selectedType = type.key"
                    class="flex items-center justify-center rounded-full w-9 h-9 text-gray-600 hover:text-pink-600 transition"
                    :class="selectedType === type.key ? 'bg-pink-100 text-pink-700' : 'bg-white'"
                >
                    <span class="material-symbols-rounded text-xl">{{ type.icon }}</span>
                </button>
            </div>

            <div ref="mapEl" class="absolute inset-0 w-full h-full"></div>
        </div>

        <!-- Liste des lieux -->
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-4 overflow-y-auto max-h-[400px]">
            <h3 class="text-lg font-semibold text-gray-700 mb-3 flex justify-between">
                Lieux à proximité
                <span v-if="isLoading" class="text-sm text-gray-400">Chargement...</span>
            </h3>

            <ul v-if="pois.length" class="space-y-2">
                <li
                    v-for="poi in pois"
                    :key="poi.id"
                    class="p-3 rounded-lg hover:bg-gray-50 border border-gray-100 transition transform hover:-translate-y-[1px] hover:shadow-sm"
                >
                    <div class="flex justify-between items-start">
                        <div class="max-w-[85%]">
                            <p class="font-semibold text-gray-800 text-[15px] leading-snug">
                                {{ poi.name }}
                            </p>
                            <p class="text-sm text-gray-500 mt-0.5">
                                {{ poi.category || 'Lieu d’intérêt' }}
                            </p>
                            <p v-if="poi.address" class="text-xs text-gray-400 mt-0.5 truncate">
                                {{ poi.address }}
                            </p>
                        </div>

                        <button
                            @click="emit('select-poi', poi)"
                            class="text-sm text-pink-600 hover:text-pink-700 font-medium"
                        >
                            ➕
                        </button>
                    </div>
                </li>
            </ul>

            <p v-else class="text-gray-500 italic text-sm mt-4">
                Aucun lieu trouvé à proximité.
            </p>
        </div>
    </div>
</template>

<style scoped>
.material-symbols-rounded {
    font-variation-settings:
        "FILL" 1,
        "wght" 400,
        "GRAD" 0,
        "opsz" 24;
}
</style>
