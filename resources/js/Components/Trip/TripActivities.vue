<template>
    <div class="flex w-full h-full">
        <!-- Sidebar gauche -->
        <aside class="w-1/4 bg-gray-50 border-r border-gray-200 overflow-y-auto p-4">
            <h2 class="text-lg font-semibold mb-4">Vos journées</h2>
            <div class="space-y-2">
                <button
                    v-for="(day, index) in days"
                    :key="index"
                    @click="activeDay = index"
                    class="w-full text-left p-3 rounded-lg transition font-medium"
                    :class="activeDay === index
              ? 'bg-blue-600 text-white shadow-md'
              : 'bg-white hover:bg-gray-100 text-gray-800 border border-gray-200'"
                >
                    <div class="flex justify-between items-center">
                        <span>Jour {{ index + 1 }}</span>
                        <span class="text-sm text-gray-500">{{ formatDate(day.date) }}</span>
                    </div>
                    <p class="text-sm text-gray-400 truncate">
                        📍 {{ day.location || 'Localisation inconnue' }}
                    </p>
                </button>
            </div>
        </aside>

        <!-- Colonne droite -->
        <section class="flex-1 p-6 overflow-y-auto">
            <div class="flex justify-between items-center mb-2">
                <h2 class="text-2xl font-bold">
                    Jour {{ activeDay + 1 }} - {{ formatDate(days[activeDay]?.date) }}
                </h2>
                <button
                    @click="openModal(activeDay)"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition"
                >
                    + Ajouter une activité
                </button>
            </div>

            <!-- Rappel localisation -->
            <p class="text-gray-600 mb-6">
                📍 {{ days[activeDay]?.location || 'Localisation inconnue' }}
            </p>

            <!-- Liste d'activités -->
            <div v-if="activitiesByDay[activeDay]?.length" class="space-y-4">
                <div
                    v-for="(activity, i) in activitiesByDay[activeDay]"
                    :key="activity.id"
                    class="bg-white shadow rounded-lg p-4 border border-gray-100"
                >
                    <div class="flex items-center gap-3 mb-2">
            <span class="text-sm font-medium text-gray-500">
              {{ formatTime(activity.start_at) }}
            </span>
                        <h3 class="text-lg font-semibold">{{ activity.title }}</h3>
                    </div>

                    <p class="text-gray-600 text-sm">{{ activity.description }}</p>

                    <div class="flex flex-wrap gap-3 mt-3 text-sm text-gray-500">
                        <span>📍 {{ activity.location || activity.step_location }}</span>
                        <span v-if="activity.cost">💰 {{ activity.cost }} {{ activity.currency }}</span>
                        <span v-if="activity.category">🏷️ {{ activity.category }}</span>
                        <span v-if="activity.external_link">
              🔗 <a :href="activity.external_link" target="_blank" class="text-blue-600 hover:underline">
                Lien
              </a>
            </span>
                    </div>
                </div>
            </div>

            <!-- Si aucune activité -->
            <div v-else class="text-gray-500 italic">
                Aucune activité prévue pour ce jour.
            </div>
        </section>

        <!-- Modal -->
        <ActivityModal
            v-if="showModal"
            :show="showModal"
            :step-id="days[activeDay]?.step_id"
            :day-date="days[activeDay]?.date.toISOString().split('T')[0]"
            @close="showModal = false"
            @created="reloadActivities"
        />
    </div>
</template>

<script setup>
import { ref, computed } from "vue"
import { router } from "@inertiajs/vue3"
import ActivityModal from "@/Components/ActivityModal.vue"

const props = defineProps({
    steps: { type: Array, required: true },
    activities: { type: Array, default: () => [] },
})

const activeDay = ref(0)
const showModal = ref(false)

// Génération des jours entre la première et la dernière étape
const days = computed(() => {
    if (!props.steps.length) return []
    const startDate = new Date(props.steps[0].start_date)
    const endDate = new Date(props.steps[props.steps.length - 1].end_date)

    const out = []
    for (let d = new Date(startDate); d <= endDate; d.setDate(d.getDate() + 1)) {
        const currentDate = new Date(d)

        const step = props.steps.find((step) => {
            const s = new Date(step.start_date)
            const e = new Date(step.end_date)
            return currentDate >= s && currentDate <= e
        })

        out.push({
            date: new Date(currentDate),
            location: step?.location || null,
            step_id: step?.id || null,
        })
    }
    return out
})

// Répartition des activités par jour
const activitiesByDay = computed(() => {
    return days.value.map((day) => {
        const dayStr = day.date.toISOString().split("T")[0]
        return props.activities.filter((a) => a.date === dayStr)
    })
})

function formatDate(date) {
    if (!date) return ""
    return new Date(date).toLocaleDateString("fr-FR", {
        weekday: "short",
        day: "numeric",
        month: "short",
    })
}

function formatTime(dateStr) {
    if (!dateStr) return ""
    return new Date(dateStr).toLocaleTimeString("fr-FR", {
        hour: "2-digit",
        minute: "2-digit",
    })
}

function openModal(dayIndex) {
    activeDay.value = dayIndex
    showModal.value = true
}

function reloadActivities() {
    router.reload({ only: ["activities"] }) // recharge uniquement les activités
}
</script>
