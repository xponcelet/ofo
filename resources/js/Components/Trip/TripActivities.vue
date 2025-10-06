<script setup>
import { ref, computed } from "vue"
import { router } from "@inertiajs/vue3"
import ActivityModal from "@/Components/ActivityModal.vue"
import ActivityMapPreview from "@/Components/ActivityMapPreview.vue"

const props = defineProps({
    days: { type: Array, required: true },
    activities: { type: Array, default: () => [] },
})

const activeDay = ref(0)
const showModal = ref(false)
const editActivity = ref(null)

// ====================
// Groupement par jour
// ====================
const activitiesByDay = computed(() => {
    return props.days.map((day) => {
        const dayStr = new Date(day.date).toISOString().split("T")[0]
        return props.activities.filter((a) => a.date === dayStr)
    })
})

// ====================
// Helpers d’affichage
// ====================
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

// ====================
// Gestion modals
// ====================
function openCreateModal(dayIndex) {
    activeDay.value = dayIndex
    editActivity.value = null
    showModal.value = true
}

function openEditModal(activity) {
    editActivity.value = activity
    showModal.value = true
}

// ====================
// Rechargement / Suppression
// ====================
function reloadActivities() {
    router.reload({ only: ["activities"] })
}

function deleteActivity(activityId) {
    if (!confirm("Voulez-vous vraiment supprimer cette activité ?")) return

    router.delete(route("activities.destroy", activityId), {
        preserveScroll: true,
        onSuccess: () => reloadActivities(),
    })
}
</script>

<template>
    <div class="grid grid-cols-1 lg:grid-cols-[300px_1fr] gap-6 h-full">
        <!-- =======================
             📅 Sidebar : Journées
        ======================== -->
        <aside class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-y-auto">
            <h2 class="px-5 py-4 text-sm font-semibold text-gray-700 border-b border-gray-100">
                Vos journées
            </h2>
            <nav class="p-3 space-y-2">
                <button
                    v-for="(day, index) in days"
                    :key="index"
                    @click="activeDay = index"
                    class="w-full text-left rounded-xl px-4 py-3 transition font-medium"
                    :class="activeDay === index
                        ? 'bg-emerald-50 border border-emerald-300 text-emerald-800 shadow-sm'
                        : 'bg-white border border-gray-200 hover:bg-gray-50 text-gray-800'">
                    <div class="flex justify-between items-center">
                        <span>Jour {{ index + 1 }}</span>
                        <span class="text-xs text-gray-500">{{ formatDate(day.date) }}</span>
                    </div>
                    <p class="text-sm text-gray-500 truncate">
                        📍 {{ day.location || 'Localisation inconnue' }}
                    </p>
                </button>
            </nav>
        </aside>

        <!-- =======================
             🗺️ Colonne principale
        ======================== -->
        <section class="flex flex-col gap-6 overflow-y-auto">
            <!-- Header du jour actif -->
            <div class="flex items-center justify-between border-b pb-3">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">
                        Jour {{ activeDay + 1 }} — {{ formatDate(days[activeDay]?.date) }}
                    </h2>
                    <p class="text-gray-500">
                        📍 {{ days[activeDay]?.location || "Localisation inconnue" }}
                    </p>
                </div>
                <button
                    @click="openCreateModal(activeDay)"
                    class="px-5 py-2.5 bg-pink-600 text-white rounded-lg shadow hover:bg-pink-700 transition">
                    ➕ Ajouter une activité
                </button>
            </div>

            <!-- 🗺️ Carte du jour actif -->
            <div v-if="days[activeDay]?.step" class="rounded-xl overflow-hidden">
                <ActivityMapPreview
                    :step="days[activeDay].step"
                    :activities="activitiesByDay[activeDay]" />
            </div>

            <!-- 🏷️ Liste des activités -->
            <div v-if="activitiesByDay[activeDay]?.length" class="space-y-4">
                <div
                    v-for="activity in activitiesByDay[activeDay]"
                    :key="activity.id"
                    class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 transition hover:shadow-md">

                    <!-- Titre + boutons -->
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">
                                {{ activity.title }}
                            </h3>
                            <p class="text-sm text-gray-500">
                                {{ formatTime(activity.start_at) }}
                                <span v-if="activity.cost" class="ml-2">
                                    • 💰 {{ activity.cost }} {{ activity.currency }}
                                </span>
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <button
                                @click="openEditModal(activity)"
                                class="text-gray-500 hover:text-pink-600 transition"
                                title="Éditer">
                                ✏️
                            </button>
                            <button
                                @click="deleteActivity(activity.id)"
                                class="text-gray-400 hover:text-red-600 transition"
                                title="Supprimer">
                                🗑️
                            </button>
                        </div>
                    </div>

                    <!-- Catégorie -->
                    <span
                        v-if="activity.category"
                        class="text-xs bg-pink-100 text-pink-700 font-medium px-2 py-1 rounded-full inline-block mb-2">
                        {{ activity.category }}
                    </span>

                    <!-- Description -->
                    <p v-if="activity.description" class="text-gray-700 text-sm mb-3">
                        {{ activity.description }}
                    </p>

                    <!-- Détails -->
                    <div class="flex flex-wrap gap-3 text-sm text-gray-500">
                        <span>📍 {{ activity.location || activity.step_location }}</span>
                        <a
                            v-if="activity.external_link"
                            :href="activity.external_link"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-pink-600 hover:underline">
                            🔗 Lien
                        </a>
                    </div>
                </div>
            </div>

            <!-- Aucune activité -->
            <div v-else class="text-gray-500 italic">
                Aucune activité prévue pour ce jour.
            </div>
        </section>

        <!-- 🔹 Modal ajout / édition -->
        <ActivityModal
            v-if="showModal"
            :show="showModal"
            :step-id="days[activeDay]?.step_id"
            :day-date="days[activeDay]?.date"
            :activity="editActivity"
            @close="showModal = false"
            @created="reloadActivities"
            @updated="reloadActivities" />
    </div>
</template>
