<script setup>
import { onMounted, ref } from 'vue'
import mapboxgl from 'mapbox-gl'

// Props et events
const props = defineProps({
    steps: Array,
})

const emit = defineEmits(['update:distance'])

const mapElement = ref(null)
const map = ref(null)

// Formule de Haversine
function haversineDistance(coord1, coord2) {
    const R = 6371 // rayon de la Terre en km
    const toRad = deg => deg * Math.PI / 180

    const [lon1, lat1] = coord1
    const [lon2, lat2] = coord2

    const dLat = toRad(lat2 - lat1)
    const dLon = toRad(lon2 - lon1)

    const a =
        Math.sin(dLat / 2) ** 2 +
        Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
        Math.sin(dLon / 2) ** 2

    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a))
    return R * c
}

function getTotalDistance(coords) {
    let total = 0
    for (let i = 1; i < coords.length; i++) {
        total += haversineDistance(coords[i - 1], coords[i])
    }
    return total
}

onMounted(() => {
    mapboxgl.accessToken = import.meta.env.VITE_MAPBOX_KEY

    map.value = new mapboxgl.Map({
        container: mapElement.value,
        style: 'mapbox://styles/mapbox/streets-v12',
        center: [2.35, 48.85],
        zoom: 4,
    })

    map.value.on('load', async () => {
        // Filtrage des étapes valides
        const validSteps = props.steps.filter(
            s => s.latitude !== null && s.longitude !== null
        )
        const coords = validSteps.map(s => [s.longitude, s.latitude])

        // Marqueurs
        validSteps.forEach((step, i) => {
            new mapboxgl.Marker()
                .setLngLat([step.longitude, step.latitude])
                .setPopup(new mapboxgl.Popup().setText(step.title))
                .addTo(map.value)
        })

        if (coords.length < 2) return

        // Calcul de la distance totale à vol d’oiseau
        const totalDistance = getTotalDistance(coords)
        emit('update:distance', totalDistance.toFixed(1))

        // Centrer la carte
        const bounds = new mapboxgl.LngLatBounds()
        coords.forEach(c => bounds.extend(c))
        map.value.fitBounds(bounds, { padding: 60 })

        if (totalDistance > 5000) {
            // 🔶 Ligne à vol d’oiseau
            map.value.addSource('route', {
                type: 'geojson',
                data: {
                    type: 'Feature',
                    geometry: {
                        type: 'LineString',
                        coordinates: coords,
                    },
                },
            })

            map.value.addLayer({
                id: 'route-line',
                type: 'line',
                source: 'route',
                layout: { 'line-join': 'round', 'line-cap': 'round' },
                paint: {
                    'line-color': '#f97316', // orange
                    'line-width': 3,
                    'line-dasharray': [2, 4],
                },
            })

            console.warn('Itinéraire à vol d’oiseau affiché (trop grande distance).')
            return
        }

        // 🔵 Appel Directions API pour un itinéraire routier
        const queryString = coords.map(c => c.join(',')).join(';')
        const url = `https://api.mapbox.com/directions/v5/mapbox/driving/${queryString}?geometries=geojson&access_token=${mapboxgl.accessToken}`

        try {
            const response = await fetch(url)
            const data = await response.json()

            if (!data.routes || !data.routes.length) {
                console.warn('Aucune route trouvée.')
                return
            }

            const route = data.routes[0].geometry

            map.value.addSource('route', {
                type: 'geojson',
                data: {
                    type: 'Feature',
                    geometry: route,
                },
            })

            map.value.addLayer({
                id: 'route-line',
                type: 'line',
                source: 'route',
                layout: { 'line-join': 'round', 'line-cap': 'round' },
                paint: {
                    'line-color': '#3b82f6', // bleu
                    'line-width': 5,
                },
            })
        } catch (e) {
            console.error('Erreur Directions API :', e)
        }
    })
})
</script>

<template>
    <div ref="mapElement" class="w-full h-96 rounded shadow" />
</template>
