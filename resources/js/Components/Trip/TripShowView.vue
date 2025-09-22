<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
    tripMeta: { type: Object, required: true },
    steps: { type: Array, default: () => [] },
    initialActiveId: [Number, String],
    tripDescription: { type: String, default: '' },
    tripDescriptionIsHtml: { type: Boolean, default: false },
})

const activeId = ref(props.initialActiveId ?? props.steps?.[0]?.id ?? null)
const activeStep = computed(() => props.steps.find(s => s.id === activeId.value) || null)
watch(() => props.steps, (arr) => {
    if (!arr?.length) { activeId.value = null; return }
    if (!arr.some(s => s.id === activeId.value)) activeId.value = arr[0].id
})

// MAPBOX
let map = null, markers = []
const routeLayerId = 'trip-route', routeSourceId = 'trip-route-source'
const mapEl = ref(null)

const toLngLat = (s) => [Number(s?.coords?.lng), Number(s?.coords?.lat)]

function drawRoute() {
    if (!map) return
    const coords = props.steps.map(toLngLat).filter(([x,y]) => isFinite(x)&&isFinite(y))
    if (map.getSource(routeSourceId)) {
        map.getSource(routeSourceId).setData({ type:'Feature', geometry:{ type:'LineString', coordinates: coords }})
    } else {
        map.addSource(routeSourceId, { type:'geojson', data:{ type:'Feature', geometry:{ type:'LineString', coordinates: coords }}})
    }
    if (!map.getLayer(routeLayerId)) {
        map.addLayer({ id: routeLayerId, type:'line', source: routeSourceId,
            paint: { 'line-color': '#176f55', 'line-width': 3, 'line-dasharray': [2,2] }})
    }
}

function placeMarkers() {
    if (!map) return
    markers.forEach(m => m.remove()); markers = []
    props.steps.forEach((s, i) => {
        const [lng, lat] = toLngLat(s)
        if (!isFinite(lng) || !isFinite(lat)) return
        const el = document.createElement('button')
        el.className = 'rounded-full shadow ring-1 ring-black/10 bg-white text-gray-900 text-xs font-medium'
        Object.assign(el.style, { width:'34px', height:'34px', display:'flex', alignItems:'center', justifyContent:'center' })
        el.textContent = String(s.day ?? i+1)
        el.addEventListener('click', () => (activeId.value = s.id))
        const marker = new window.mapboxgl.Marker({ element: el, anchor: 'center' }).setLngLat([lng, lat]).addTo(map)
        markers.push({ stepId:s.id, marker, el })
    })
    highlightActive()
}

function highlightActive() {
    markers.forEach(({ stepId, el }) => {
        const active = stepId === activeId.value
        el.style.transform = active ? 'scale(1.08)' : 'scale(1.0)'
        el.style.border = active ? '2px solid #22c55e' : '1px solid rgba(0,0,0,0.1)'
    })
    const s = props.steps.find(x => x.id === activeId.value)
    if (s?.coords && map) map.easeTo({ center: toLngLat(s), zoom: Math.max(map.getZoom(), 7), duration: 600 })
}

function fitAll() {
    const pts = props.steps.map(toLngLat).filter(([x,y]) => isFinite(x)&&isFinite(y))
    if (!pts.length) return
    const b = new window.mapboxgl.LngLatBounds(pts[0], pts[0]); pts.forEach(p => b.extend(p))
    map.fitBounds(b, { padding: { top:40, right:40, bottom:40, left:40 } })
}

onMounted(() => {
    if (!window.mapboxgl?.Map) return
    map = new window.mapboxgl.Map({
        container: mapEl.value,
        style: 'mapbox://styles/mapbox/streets-v12',
        center: [2,48],
        zoom: 3.5,
    })
    map.addControl(new window.mapboxgl.NavigationControl({ visualizePitch: true }), 'top-right')
    map.on('load', () => { drawRoute(); placeMarkers(); fitAll() })
})
onBeforeUnmount(() => { if (map) map.remove() })
watch(() => props.steps, () => { if (!map) return; drawRoute(); placeMarkers(); fitAll() }, { deep:true })
watch(activeId, () => { if (!map) return; highlightActive() })
</script>

<template>
    <!-- ✅ Layout Résumé : Étapes (1/4) + Carte (3/4), puis description full width -->
    <div class="space-y-6">
        <!-- Étapes + Carte -->
        <div class="grid grid-cols-1 lg:grid-cols-[1fr_3fr] gap-4 lg:gap-6">
            <!-- Étapes -->
            <aside class="lg:h-[calc(100vh-220px)]">
                <div class="rounded-2xl border border-gray-200 bg-white h-full">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h2 class="text-sm font-semibold tracking-wide text-gray-700">Étapes</h2>
                    </div>
                    <nav class="max-h-[60vh] overflow-y-auto p-2">
                        <button
                            v-for="s in steps" :key="s.id" @click="activeId = s.id"
                            class="w-full text-left rounded-xl px-3 py-3 mb-1 transition hover:bg-gray-50
                     flex items-center gap-3"
                            :class="s.id === activeId ? 'bg-emerald-50 ring-1 ring-emerald-200' : ''"
                        >
                            <div class="flex h-8 w-8 items-center justify-center rounded-full border"
                                 :class="s.id === activeId ? 'border-emerald-400 text-emerald-700' : 'border-gray-300 text-gray-600'">
                                {{ s.day ?? '?' }}
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-gray-900">{{ s.short_title }}</p>
                                <p class="truncate text-xs text-gray-500">{{ s.title }}</p>
                            </div>
                        </button>
                    </nav>
                </div>
            </aside>

            <!-- Carte -->
            <section class="lg:h-[calc(100vh-220px)]">
                <div class="rounded-2xl border border-gray-200 overflow-hidden bg-white h-full">
                    <div ref="mapEl" class="w-full h-full"></div>
                </div>
            </section>
        </div>

        <!-- Description -->
        <section class="rounded-2xl border border-gray-200 bg-white p-6">
            <h3 class="text-lg font-semibold mb-3">Description du voyage</h3>
            <div v-if="tripDescription" class="prose prose-sm max-w-none">
                <div v-if="tripDescriptionIsHtml" v-html="tripDescription"></div>
                <p v-else class="whitespace-pre-line text-gray-700">{{ tripDescription }}</p>
            </div>
            <p v-else class="text-sm text-gray-500">Aucune description pour ce voyage.</p>
        </section>
    </div>
</template>
