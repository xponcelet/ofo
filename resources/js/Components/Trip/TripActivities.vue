<template>
    <div class="flex w-full h-full">
        <!-- Sidebar gauche : liste des jours -->
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

        <!-- Colonne droite : activités du jour -->
        <section class="flex-1 p-6 overflow-y-auto">
            <div class="flex justify-between items-center mb-2">
                <h2 class="text-2xl font-bold">
                    Jour {{ activeDay + 1 }} - {{ formatDate(days[activeDay]?.date) }}
                </h2>
                <button
                    @click="addActivity(activeDay)"
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
                    :key="i"
                    class="bg-white shadow rounded-lg p-4 border border-gray-100"
                >
                    <div class="flex items-center gap-3 mb-2">
                        <span class="text-sm font-medium text-gray-500">{{ activity.time }}</span>
                        <h3 class="text-lg font-semibold">{{ activity.name }}</h3>
                    </div>
                    <p class="text-gray-600 text-sm">{{ activity.description }}</p>
                    <div class="flex flex-wrap gap-3 mt-3 text-sm text-gray-500">
                        <span v-if="activity.location">📍 {{ activity.location }}</span>
                        <span v-if="activity.duration">⏱ {{ activity.duration }}</span>
                        <span v-if="activity.status">✅ {{ activity.status }}</span>
                    </div>
                </div>
            </div>

            <!-- Si aucune activité -->
            <div v-else class="text-gray-500 italic">
                Aucune activité prévue pour ce jour.
            </div>
        </section>
    </div>
</template>

<script setup>
import { ref, computed } from "vue"

const props = defineProps({
    steps: { type: Array, required: true },        // étapes du trip
    activities: { type: Array, default: () => [] } // activités
})

const activeDay = ref(0)

// Génération des jours entre première et dernière étape
const days = computed(() => {
    if (!props.steps.length) return []

    const startDate = new Date(props.steps[0].start_date)
    const endDate = new Date(props.steps[props.steps.length - 1].end_date)

    const out = []
    for (let d = new Date(startDate); d <= endDate; d.setDate(d.getDate() + 1)) {
        const currentDate = new Date(d)

        // étape couvrant ce jour
        const step = props.steps.find(step => {
            const s = new Date(step.start_date)
            const e = new Date(step.end_date)
            return currentDate >= s && currentDate <= e
        })

        out.push({
            date: new Date(currentDate),
            location: step?.location || null // 🔥 localisation depuis l’étape
        })
    }
    return out
})

// Répartition des activités par jour
const activitiesByDay = computed(() => {
    return days.value.map(day => {
        const dayStr = day.date.toISOString().split("T")[0]
        return props.activities.filter(a => a.date === dayStr)
    })
})

function formatDate(date) {
    if (!date) return ""
    return new Date(date).toLocaleDateString("fr-FR", {
        weekday: "short",
        day: "numeric",
        month: "short"
    })
}

function addActivity(dayIndex) {
    console.log("Ajouter une activité pour le jour", dayIndex + 1, days.value[dayIndex])
}
</script>
