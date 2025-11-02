<script setup>
import { Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import MapboxAutocomplete from '@/Components/MapboxAutocomplete.vue'
import StepMapPreview from '@/Components/Step/StepMapPreview.vue'
import { t } from '@/Composables/useTranslations'

const props = defineProps({
    canCreate: { type: Boolean, default: false },
})

// 🌍 Valeurs par défaut : Espagne
const DEFAULT_PLACE = {
    label: 'Espagne',
    center: [-3.7038, 40.4168], // Madrid
    bbox: null,
}

// 🔍 Input prérempli
const query = ref(DEFAULT_PLACE.label)

// 📍 Sélection initiale = Espagne
const selected = ref(DEFAULT_PLACE)

// 🧭 Fonction appelée lors d'une sélection Mapbox
function onSelect(place) {
    if (!place) return

    const center = place.center || place?.geometry?.coordinates
    selected.value = {
        label: place.place_name || place.text || query.value,
        center: Array.isArray(center) ? center : DEFAULT_PLACE.center,
        bbox: place.bbox ?? null,
    }
    query.value = selected.value.label || ''
}
</script>

<template>
    <section
        class="flex flex-col h-full rounded-2xl border border-gray-200 bg-white shadow-md hover:shadow-lg transition overflow-hidden"
    >
        <!-- En-tête -->
        <div class="p-6">
            <h2 class="text-2xl font-semibold text-gray-900 text-center">
                {{ t('dashboardSearch.title') }}
            </h2>
            <p class="text-gray-600 text-center mt-1">
                {{ t('dashboardSearch.subtitle') }}
            </p>

            <!-- Champ de recherche -->
            <label class="block text-sm text-gray-700 mt-6 mb-2">
                {{ t('dashboardSearch.label') }}
            </label>

            <MapboxAutocomplete
                v-model="query"
                @select="onSelect"
                @place-selected="onSelect"
                :placeholder="t('dashboardSearch.placeholder')"
                class="w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-2.5 shadow-sm
               focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
            />

            <!-- Carte -->
            <div class="mt-5">
                <StepMapPreview
                    v-if="selected?.center"
                    :latitude="selected.center[1]"
                    :longitude="selected.center[0]"
                    :zoom="5"
                    class="w-full h-56 md:h-64 rounded-xl overflow-hidden border border-gray-200"
                />
            </div>
        </div>

        <!-- Bouton principal -->
        <div class="mt-auto p-6 pt-0 flex items-center justify-center">
            <Link
                v-if="canCreate"
                :href="route('trips.start')"
                class="inline-flex items-center justify-center rounded-full px-6 py-3
               bg-blue-600 text-white font-medium shadow-sm
               hover:bg-blue-700 hover:shadow-md transition"
            >
                {{ t('dashboardSearch.createTrip') }}
            </Link>

            <Link
                v-else
                :href="route('register')"
                class="inline-flex items-center justify-center rounded-full px-6 py-3
               bg-gray-100 text-gray-700 font-medium hover:bg-gray-200 transition"
            >
                {{ t('dashboardSearch.createAccount') }}
            </Link>
        </div>
    </section>
</template>
