import mapboxgl from 'mapbox-gl'
import { ref, onBeforeUnmount } from 'vue'

export function useMapbox() {
    const map = ref(null)

    // Initialise Mapbox avec ton token
    const initMap = (container, options = {}) => {
        if (!import.meta.env.VITE_MAPBOX_KEY) {
            console.error('⚠️ Mapbox token manquant : vérifie ton .env (VITE_MAPBOX_KEY)')
            return null
        }

        mapboxgl.accessToken = import.meta.env.VITE_MAPBOX_KEY

        map.value = new mapboxgl.Map({
            container,
            style: options.style ?? 'mapbox://styles/mapbox/streets-v12',
            center: options.center ?? [2, 48], // Europe par défaut
            zoom: options.zoom ?? 3.5,
            ...options,
        })

        if (options.navigation !== false) {
            map.value.addControl(new mapboxgl.NavigationControl(), 'top-right')
        }

        return map.value
    }

    const destroyMap = () => {
        if (map.value) {
            map.value.remove()
            map.value = null
        }
    }

    onBeforeUnmount(() => {
        destroyMap()
    })

    return {
        map,
        initMap,
        destroyMap,
        mapboxgl, // exposé si tu veux créer des Markers/Popups facilement
    }
}
