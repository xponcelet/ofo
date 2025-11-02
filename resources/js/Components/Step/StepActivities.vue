<script setup>
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import ActivityModal from '@/Components/ActivityModal.vue'

const props = defineProps({
    step: { type: Object, required: true },
    activities: { type: Array, default: () => [] },
})

const showModal = ref(false)
const selectedActivity = ref(null)

function openCreateModal() {
    selectedActivity.value = null
    showModal.value = true
}

function openEditModal(activity) {
    selectedActivity.value = activity
    showModal.value = true
}

function handleCreated() {
    router.reload({ only: ['activities'] })
}
function handleUpdated() {
    router.reload({ only: ['activities'] })
}
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">
                Activités de l’étape
            </h2>
            <button
                @click="openCreateModal"
                class="flex items-center gap-2 bg-pink-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-pink-700 transition"
            >
                <span class="material-symbols-rounded text-lg">add</span>
                Ajouter une activité
            </button>
        </div>

        <!-- Liste -->
        <div v-if="activities.length" class="grid md:grid-cols-2 gap-4">
            <div
                v-for="activity in activities"
                :key="activity.id"
                class="p-4 bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow transition flex flex-col justify-between"
            >
                <div>
                    <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                        <span class="material-symbols-rounded text-pink-500 text-lg">
                            {{ activity.category === 'restaurant' ? 'restaurant' :
                            activity.category === 'hotel' ? 'hotel' :
                                activity.category === 'bar' ? 'local_bar' :
                                    activity.category === 'museum' ? 'museum' :
                                        activity.category === 'park' ? 'park' :
                                            activity.category === 'attraction' ? 'star' : 'location_on' }}
                        </span>
                        {{ activity.title }}
                    </h3>
                    <p v-if="activity.location" class="text-gray-500 text-sm mt-1">
                        {{ activity.location }}
                    </p>
                    <p v-if="activity.date" class="text-xs text-gray-400 mt-1">
                        {{ new Date(activity.date).toLocaleDateString('fr-FR') }}
                    </p>
                    <p v-if="activity.description" class="text-gray-600 text-sm mt-3 line-clamp-3">
                        {{ activity.description }}
                    </p>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <a
                        v-if="activity.external_link"
                        :href="activity.external_link"
                        target="_blank"
                        class="text-sm text-gray-500 hover:text-gray-700 underline"
                    >
                        Voir le lien
                    </a>
                    <button
                        @click="openEditModal(activity)"
                        class="text-sm text-pink-600 hover:text-pink-700 font-medium"
                    >
                        Modifier
                    </button>
                </div>
            </div>
        </div>

        <p v-else class="text-gray-500 text-sm italic">
            Aucune activité ajoutée pour cette étape.
        </p>

        <!-- Modal -->
        <ActivityModal
            :show="showModal"
            :step-id="step.id"
            :step="step"
            :activity="selectedActivity"
            @close="showModal = false"
            @created="handleCreated"
            @updated="handleUpdated"
        />
    </div>
</template>

<style scoped>
.material-symbols-rounded {
    font-variation-settings: "FILL" 1, "wght" 400, "GRAD" 0, "opsz" 24;
}
</style>
