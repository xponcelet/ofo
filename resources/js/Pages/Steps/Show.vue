<script setup>
import RootLayout from '@/Layouts/RootLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import PoiExplorer from '@/Components/PoiExplorer.vue'
import ActivityModal from '@/Components/ActivityModal.vue'

defineOptions({ layout: RootLayout })

const props = defineProps({
    step: Object,
    trip: Object,
    activities: Array,
})

const showModal = ref(false)
const selectedActivity = ref(null)
const selectedDate = ref(null)

function openModal(activity = null, date = null) {
    selectedActivity.value = activity
    selectedDate.value = date
    showModal.value = true
}
function closeModal() {
    showModal.value = false
    selectedActivity.value = null
    selectedDate.value = null
}

function groupByDateAndCategory(activities) {
    const grouped = {}
    for (const act of activities) {
        const date = act.start_at ? act.start_at.slice(0, 10) : 'inconnue'
        const cat = act.category || 'Autres'
        if (!grouped[date]) grouped[date] = {}
        if (!grouped[date][cat]) grouped[date][cat] = []
        grouped[date][cat].push(act)
    }
    return grouped
}
const activitiesGrouped = computed(() => groupByDateAndCategory(props.activities || []))

function generateDays(start, end) {
    if (!start || !end) return []
    const days = []
    const cur = new Date(start)
    const endDate = new Date(end)
    while (cur <= endDate) {
        days.push(cur.toISOString().slice(0, 10))
        cur.setDate(cur.getDate() + 1)
    }
    return days
}
const days = computed(() => generateDays(props.step.start_date, props.step.end_date))

function formatDayTitle(dateStr, index) {
    const options = { weekday: 'short', day: 'numeric', month: 'short' }
    return `Jour ${index + 1} – ${new Date(dateStr).toLocaleDateString('fr-FR', options)}`
}

function handlePoiSelect(poi) {
    const activity = {
        title: poi.name,
        category: poi.category,
        location: poi.address || poi.name,
    }
    openModal(activity, null)
}
</script>

<template>
    <Head :title="step.title || 'Étape'" />

    <div class="max-w-7xl mx-auto px-4 py-10 grid lg:grid-cols-2 gap-8">
        <!-- Colonne gauche : infos + activités -->
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-semibold flex items-center gap-2">
                        <span class="material-symbols-rounded text-pink-600 text-xl">map</span>
                        {{ step.title || 'Étape sans titre' }}
                    </h1>
                    <p class="text-gray-500 mt-1">
                        {{ step.location }} •
                        {{ new Date(step.start_date).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' }) }}
                        →
                        {{ new Date(step.end_date).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' }) }}
                        ({{ step.nights }} nuits)
                    </p>
                </div>
                <button
                    @click="router.visit(route('steps.edit', { step: step.id }))"
                    class="text-sm text-pink-600 hover:text-pink-700 font-medium flex items-center gap-1"
                >
                    <span class="material-symbols-rounded text-[18px]">edit</span>
                    Modifier
                </button>
            </div>

            <!-- Informations -->
            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                <h2 class="font-semibold text-gray-700 mb-2 flex items-center gap-2">
                    <span class="material-symbols-rounded text-pink-500">info</span>
                    Informations sur l’étape
                </h2>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li><span class="material-symbols-rounded text-sm text-pink-400 align-middle">location_on</span> {{ step.location || 'Lieu non défini' }}</li>
                    <li><span class="material-symbols-rounded text-sm text-pink-400 align-middle">directions_car</span> Transport : {{ step.transport_mode || '—' }}</li>
                    <li v-if="step.distance_to_next"><span class="material-symbols-rounded text-sm text-pink-400 align-middle">straighten</span> {{ step.distance_to_next }} km jusqu’à la suivante</li>
                </ul>
            </div>

            <!-- Activités par jour -->
            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <span class="material-symbols-rounded text-pink-500">event</span>
                    Activités
                </h2>

                <div v-if="!days.length" class="text-sm text-gray-500 italic">
                    Aucune date définie pour cette étape.
                </div>

                <div v-else class="relative border-l border-gray-200 ml-4">
                    <div v-for="(day, i) in days" :key="day" class="relative pl-6 pb-8">
                        <div class="absolute -left-[7px] top-1 w-3 h-3 bg-pink-500 rounded-full"></div>
                        <h3 class="font-semibold text-gray-800 mb-3">
                            {{ formatDayTitle(day, i) }}
                        </h3>

                        <!-- Activités groupées par catégorie -->
                        <div v-if="activitiesGrouped[day]" class="space-y-4">
                            <div
                                v-for="(list, cat) in activitiesGrouped[day]"
                                :key="cat"
                                class="border border-gray-100 rounded-lg p-3 bg-pink-50/30"
                            >
                                <h4 class="text-sm font-semibold text-pink-700 mb-2 flex items-center gap-1">
                                    <span class="material-symbols-rounded text-[18px] text-pink-500">label</span>
                                    {{ cat }}
                                </h4>

                                <ul class="space-y-2">
                                    <li
                                        v-for="a in list"
                                        :key="a.id"
                                        class="bg-white rounded-lg border border-gray-200 p-3 flex justify-between items-start hover:shadow-sm transition"
                                    >
                                        <div>
                                            <p class="font-medium text-gray-800">{{ a.title }}</p>
                                            <p v-if="a.location" class="text-xs text-gray-500 mt-0.5">
                                                {{ a.location }}
                                            </p>
                                        </div>
                                        <button
                                            @click="openModal(a, day)"
                                            class="text-sm text-pink-600 hover:text-pink-700 font-medium"
                                        >
                                            <span class="material-symbols-rounded text-[18px] align-middle">edit</span>
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Ajouter -->
                        <div class="mt-3">
                            <button
                                @click="openModal(null, day)"
                                class="flex items-center gap-1 text-sm text-gray-500 hover:text-pink-600 transition"
                            >
                                <span class="material-symbols-rounded text-[18px]">add_circle</span>
                                Ajouter une activité
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Colonne droite : exploration -->
        <div>
            <PoiExplorer
                :latitude="step.latitude"
                :longitude="step.longitude"
                @select-poi="handlePoiSelect"
            />
        </div>
    </div>

    <!-- Modal -->
    <ActivityModal
        :show="showModal"
        :step-id="step.id"
        :activity="selectedActivity"
        :step="step"
        @close="closeModal"
        @created="router.reload({ only: ['activities'] })"
        @updated="router.reload({ only: ['activities'] })"
    />
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
