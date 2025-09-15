<script setup>
import { Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import MapboxAutocomplete from '@/Components/MapboxAutocomplete.vue'
import StepMapPreview from '@/Components/StepMapPreview.vue'

const props = defineProps({
    canCreate: { type: Boolean, default: false },
})

// Valeurs par défaut
const query = ref('Espagne')
// Centre par défaut (lng, lat)
const selected = ref({ label: 'Espagne', center: [-3.7, 40.4], bbox: null })

function onSelect(place) {
    if (!place) return
    const center = place.center || place?.geometry?.coordinates
    selected.value = {
        label: place.label || place.place_name || place.text || query.value,
        center: Array.isArray(center) ? center : selected.value.center,
        bbox: place.bbox ?? null,
    }
    if (selected.value.label) {
        query.value = selected.value.label
    }
}

const previewSteps = computed(() => {
    const [lng, lat] = selected.value.center
    return [
        { id: 'preview', title: query.value, latitude: lat, longitude: lng }
    ]
})

function searchTrips() {
    router.get(
        route('public.trips.index'),
        { q: query.value || undefined },
        { preserveScroll: true, replace: true },
    )
}
</script>

<template>
    <section class="bg-white h-full flex flex-col rounded-2xl border border-gray-200 shadow-sm">
        <div class="p-6">
            <h2 class="text-2xl font-semibold text-center">Créer</h2>
            <p class="text-gray-600 text-center mt-1">Je sais où je veux aller</p>

            <label class="block text-sm text-gray-700 mt-5 mb-2 text-left">Pays, région ou ville</label>

            <!-- Selon ton composant, l’événement peut être @select OU @place-selected. On écoute les deux. -->
            <MapboxAutocomplete
                v-model="query"
                @select="onSelect"
                @place-selected="onSelect"
                :placeholder="'Espagne, Italie, Bretagne…'"
                class="w-full rounded-xl border border-gray-300 bg-white/70 px-4 py-2.5 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            />

            <div class="mt-5">
                <StepMapPreview
                    :steps="previewSteps"
                    class="w-full h-56 md:h-64 rounded-xl overflow-hidden border border-gray-200"
                />
            </div>
        </div>

        <div class="mt-auto p-6 pt-0 flex items-center justify-center gap-4">
            <Link
                :href="route('trips.start')"
                class="inline-flex items-center justify-center rounded-xl px-4 py-2 bg-gray-200 text-gray-600"
                :class="{ 'pointer-events-none opacity-50': !props.canCreate }"
            >
                Créer un voyage
            </Link>

            <button
                type="button"
                @click="searchTrips"
                class="inline-flex items-center justify-center rounded-xl px-4 py-2 bg-blue-600 text-white hover:bg-blue-700"
            >
                Chercher un voyage
            </button>
        </div>

        <div v-if="!props.canCreate" class="px-6 pb-6 -mt-2">
            <Link :href="route('register')" class="text-sm text-blue-600 hover:underline">Créer un compte</Link>
        </div>
    </section>
</template>
