<script setup>
import { Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import MapboxAutocomplete from '@/Components/MapboxAutocomplete.vue'
import StepMapPreview from '@/Components/StepMapPreview.vue'
import { t } from '@/composables/useTranslations'

const props = defineProps({
    canCreate: { type: Boolean, default: false },
})

const query = ref('Espagne')
const selected = ref({ label: 'Espagne', center: [-3.7, 40.4], bbox: null })

function onSelect(place) {
    if (!place) return
    const center = place.center || place?.geometry?.coordinates
    selected.value = {
        label: place.label || place.place_name || place.text || query.value,
        center: Array.isArray(center) ? center : selected.value.center,
        bbox: place.bbox ?? null,
    }
    if (selected.value.label) query.value = selected.value.label
}

const previewSteps = computed(() => {
    const [lng, lat] = selected.value.center
    return [{ id: 'preview', title: query.value, latitude: lat, longitude: lng }]
})

function searchTrips() {
    router.get(route('public.trips.index'), { q: query.value || undefined }, { preserveScroll: true, replace: true })
}
</script>

<template>
    <section class="flex flex-col h-full rounded-2xl border border-outline/20 bg-surface shadow-md overflow-hidden">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-on-surface text-center">
                {{ t('dashboardSearch.title') }}
            </h2>
            <p class="text-on-surface-variant text-center mt-1">
                {{ t('dashboardSearch.subtitle') }}
            </p>

            <label class="block text-sm text-on-surface mt-5 mb-2">
                {{ t('dashboardSearch.label') }}
            </label>

            <MapboxAutocomplete
                v-model="query"
                @select="onSelect"
                @place-selected="onSelect"
                :placeholder="t('dashboardSearch.placeholder')"
                class="w-full rounded-xl border border-outline/30 bg-surface-variant px-4 py-2.5 shadow-sm
                       focus:outline-none focus:ring-2 focus:ring-primary/50"
            />

            <div class="mt-5">
                <StepMapPreview
                    :steps="previewSteps"
                    class="w-full h-56 md:h-64 rounded-xl overflow-hidden border border-outline/20"
                />
            </div>
        </div>

        <!-- CTA -->
        <div class="mt-auto p-6 pt-0 flex items-center justify-center gap-4">
            <Link
                :href="route('trips.start')"
                class="inline-flex items-center justify-center rounded-full px-5 py-2.5
                       bg-surface-variant text-on-surface-variant font-medium shadow-sm
                       hover:bg-surface-variant/70 hover:shadow transition"
                :class="{ 'pointer-events-none opacity-50': !props.canCreate }"
            >
                {{ t('dashboardSearch.createTrip') }}
            </Link>

            <button
                type="button"
                @click="searchTrips"
                class="inline-flex items-center justify-center rounded-full px-5 py-2.5
                       bg-primary text-white font-medium shadow-sm
                       hover:bg-primary/90 hover:shadow-md transition"
            >
                {{ t('dashboardSearch.searchTrip') }}
            </button>
        </div>

        <div v-if="!props.canCreate" class="px-6 pb-6 -mt-2 text-center">
            <Link :href="route('register')" class="text-sm text-primary hover:underline">
                {{ t('dashboardSearch.createAccount') }}
            </Link>
        </div>
    </section>
</template>
