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
const activitiesByDay = computed(() =>
    props.days.map((day) => {
        const dayStr = new Date(day.date).toISOString().split("T")[0]
        return props.activities
            .filter((a) => a.date === dayStr)
            .sort((a, b) => new Date(a.start_at) - new Date(b.start_at))
    })
)

// ====================
// Helpers d’affichage
// ====================
function formatDate(date) {
    return new Date(date).toLocaleDateString("fr-FR", {
        weekday: "short",
        day: "numeric",
        month: "short",
    })
}

function formatTime(dateStr) {
    return dateStr
        ? new Date(dateStr).toLocaleTimeString("fr-FR", {
            hour: "2-digit",
            minute: "2-digit",
        })
        : ""
}

/** 🗺️ Lien Google Maps — itinéraire ou recherche */
function mapsLink(activity) {
    if (activity.latitude && activity.longitude) {
        return `https://www.google.com/maps/dir/?api=1&destination=${activity.latitude},${activity.longitude}`
    }
    if (activity.location) {
        return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(activity.location)}`
    }
    return null
}

// ====================
// Modals / Actions
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

function reloadActivities() {
    router.reload({ only: ["activities"] })
}

function deleteActivity(id) {
    if (!confirm("Supprimer cette activité ?")) return
    router.delete(route("activities.destroy", id), {
        preserveScroll: true,
        onSuccess: () => reloadActivities(),
    })
}

// ====================
// 🎯 Création depuis un POI
// ====================
function openPoiAsActivity(poi) {
    if (!poi || !poi.lat || !poi.lon) {
        console.warn("⚠️ POI sans coordonnées valides :", poi)
        return
    }

    editActivity.value = {
        title: poi.name || "Lieu sans nom",
        location: poi.name || "",
        description: poi.tags?.description || "",
        latitude: poi.lat,
        longitude: poi.lon,
        start_at: null,
        end_at: null,
        external_link: "",
        cost: null,
        currency: "EUR",
        category:
            poi.category ||
            poi.tags?.amenity ||
            poi.tags?.tourism ||
            poi.tags?.leisure ||
            "Découverte",
    }

    showModal.value = true
}
</script>

<template>
    <div class="grid grid-cols-1 lg:grid-cols-[300px_1fr] gap-6">
        <!-- =======================
             📅 Sidebar
        ======================== -->
        <aside class="rounded-2xl border border-gray-200 bg-white shadow-sm">
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
                        : 'bg-white border border-gray-200 hover:bg-gray-50 text-gray-800'"
                >
                    <div class="flex justify-between items-center">
                        <span>Jour {{ index + 1 }}</span>
                        <span class="text-xs text-gray-500">{{ formatDate(day.date) }}</span>
                    </div>
                    <p class="text-sm text-gray-500 truncate">
                        📍 {{ day.location || "Localisation inconnue" }}
                    </p>
                </button>
            </nav>
        </aside>

        <!-- =======================
             🗺️ Contenu principal
        ======================== -->
        <section class="flex flex-col gap-6">
            <!-- En-tête -->
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
                    class="px-5 py-2.5 bg-pink-600 text-white rounded-lg shadow hover:bg-pink-700 transition"
                >
                    ➕ Ajouter une activité
                </button>
            </div>

            <!-- Carte -->
            <div v-if="days[activeDay]?.step" class="rounded-xl overflow-hidden">
                <ActivityMapPreview
                    :step="days[activeDay].step"
                    :activities="activitiesByDay[activeDay]"
                    :show-activities="true"
                    @select-poi="openPoiAsActivity"
                />
            </div>

            <!-- Liste des activités -->
            <div
                v-if="activitiesByDay[activeDay]?.length"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"
            >
                <div
                    v-for="activity in activitiesByDay[activeDay]"
                    :key="activity.id"
                    class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex flex-col justify-between hover:shadow-md transition"
                >
                    <div>
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
                                    class="text-gray-500 hover:text-pink-600"
                                >
                                    ✏️
                                </button>
                                <button
                                    @click="deleteActivity(activity.id)"
                                    class="text-gray-400 hover:text-red-600"
                                >
                                    🗑️
                                </button>
                            </div>
                        </div>

                        <!-- Catégorie -->
                        <span
                            v-if="activity.category"
                            class="text-xs bg-pink-100 text-pink-700 font-medium px-2 py-1 rounded-full inline-block mb-2"
                        >
                            {{ activity.category }}
                        </span>

                        <!-- Description -->
                        <p v-if="activity.description" class="text-gray-700 text-sm mb-3 line-clamp-3">
                            {{ activity.description }}
                        </p>
                    </div>

                    <!-- Liens et infos -->
                    <div class="flex flex-wrap items-center gap-2 text-sm text-gray-500 mt-3">
                        <span>📍 {{ activity.location || "Lieu non défini" }}</span>

                        <!-- 🌍 Lien Google Maps (itinéraire ou recherche) -->
                        <a
                            v-if="mapsLink(activity)"
                            :href="mapsLink(activity)"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-emerald-600 hover:underline flex items-center gap-1"
                        >
                            <span class="material-symbols-rounded text-sm">assistant_direction</span>
                            Itinéraire
                        </a>

                        <!-- 🔗 Lien externe -->
                        <a
                            v-if="activity.external_link"
                            :href="activity.external_link"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-pink-600 hover:underline flex items-center gap-1"
                        >
                            <span class="material-symbols-rounded text-sm">link</span>
                            Site
                        </a>
                    </div>
                </div>
            </div>

            <!-- Aucun résultat -->
            <div v-else class="text-gray-500 italic">
                Aucune activité prévue pour ce jour.
            </div>
        </section>

        <!-- 🪄 Modal création / édition -->
        <ActivityModal
            v-if="showModal"
            :show="showModal"
            :step-id="days[activeDay]?.step_id"
            :step="days[activeDay]?.step"
            :day-date="days[activeDay]?.date"
            :activity="editActivity"
            @close="showModal = false"
            @created="reloadActivities"
            @updated="reloadActivities"
        />
    </div>
</template>
