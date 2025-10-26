<script setup>
import { useForm, router } from '@inertiajs/vue3'
import { onMounted, ref } from 'vue'
import StepForm from '@/Components/StepForm.vue'

const props = defineProps({
    step: Object,
    trip: Object,
    allSteps: Array,
    suggested_start: {
        type: String,
        default: null,
    },
})

// === Formulaire principal étape ===
const form = useForm({
    id: props.step.id,
    title: props.step.title || '',
    description: props.step.description || '',
    location: props.step.location || '',
    latitude: props.step.latitude,
    longitude: props.step.longitude,
    start_date: props.step.start_date || '',
    end_date: props.step.end_date || '',
    country: props.step.country || '',
    country_code: props.step.country_code || '',
    transport_mode: props.step.transport_mode || 'car',
    nights: props.step.nights ?? 0,
})

onMounted(() => {
    if (!form.start_date && props.suggested_start) {
        form.start_date = props.suggested_start
    }
})

function handleSubmit() {
    form.put(route('steps.update', props.step.id))
}

// === Gestion des activités ===
const activityForm = useForm({
    title: '',
    description: '',
    location: '',
    start_at: '',
    end_at: '',
    cost: '',
    currency: '',
    category: '',
    external_link: '',
})

const editingActivityId = ref(null)

function editActivity(activity) {
    editingActivityId.value = activity.id
    Object.assign(activityForm, {
        title: activity.title,
        description: activity.description,
        location: activity.location,
        start_at: activity.start_at,
        end_at: activity.end_at,
        cost: activity.cost,
        currency: activity.currency,
        category: activity.category,
        external_link: activity.external_link,
    })
}

function cancelEdit() {
    editingActivityId.value = null
    activityForm.reset()
}

function saveActivity() {
    if (editingActivityId.value) {
        activityForm.put(route('activities.update', editingActivityId.value), {
            preserveScroll: true,
            onSuccess: () => cancelEdit(),
        })
    } else {
        activityForm.post(route('steps.activities.store', props.step.id), {
            preserveScroll: true,
            onSuccess: () => activityForm.reset(),
        })
    }
}

function deleteActivity(activity) {
    if (!confirm('Supprimer cette activité ?')) return
    router.delete(route('activities.destroy', activity.id), { preserveScroll: true })
}
</script>

<template>
    <div class="max-w-5xl mx-auto py-10 px-4 lg:px-8">
        <!-- Titre principal -->
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                ✏️ Modifier l’étape
            </h1>
            <p class="text-sm text-gray-500">
                Voyage : <span class="font-medium text-gray-700">{{ trip.title }}</span>
            </p>
        </div>

        <!-- === Formulaire Étape === -->
        <StepForm
            :form="form"
            :on-submit="handleSubmit"
            submit-label="Enregistrer les modifications"
            :suggested-start="suggested_start"
        />

        <!-- === Section Activités === -->
        <section class="mt-12">
            <h2 class="text-lg font-semibold text-gray-900 mb-6 flex items-center gap-2">
                <span class="material-symbols-rounded text-pink-600 text-lg">explore</span>
                Activités de cette étape
            </h2>

            <!-- Liste -->
            <div v-if="step.activities?.length" class="space-y-4 mb-8">
                <div
                    v-for="a in step.activities"
                    :key="a.id"
                    class="bg-white border border-gray-200 rounded-2xl shadow-sm hover:shadow-md transition p-5 flex justify-between items-start"
                >
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900">{{ a.title }}</h3>
                        <p v-if="a.start_at" class="text-xs text-gray-500 mt-1">
                            {{ new Date(a.start_at).toLocaleString('fr-FR', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) }}
                        </p>
                        <p v-if="a.description" class="text-sm text-gray-600 mt-2">{{ a.description }}</p>
                    </div>

                    <div class="flex gap-2">
                        <button
                            @click="editActivity(a)"
                            class="flex items-center gap-1 text-sm px-3 py-1 rounded-lg border border-gray-300 hover:bg-gray-100 transition"
                        >
                            <span class="material-symbols-rounded text-sm">edit</span> Modifier
                        </button>
                        <button
                            @click="deleteActivity(a)"
                            class="flex items-center gap-1 text-sm px-3 py-1 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 transition"
                        >
                            <span class="material-symbols-rounded text-sm">delete</span> Supprimer
                        </button>
                    </div>
                </div>
            </div>

            <!-- Formulaire activité -->
            <div class="bg-gray-50 border border-gray-200 rounded-2xl shadow-sm p-6 space-y-4">
                <h3 class="text-md font-semibold text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-rounded text-sm text-blue-600">
                        {{ editingActivityId ? 'edit' : 'add' }}
                    </span>
                    {{ editingActivityId ? 'Modifier une activité' : 'Ajouter une activité' }}
                </h3>

                <form @submit.prevent="saveActivity" class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Titre *</label>
                        <input
                            v-model="activityForm.title"
                            type="text"
                            required
                            class="w-full border rounded-lg px-3 py-2 text-sm"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea
                            v-model="activityForm.description"
                            rows="2"
                            class="w-full border rounded-lg px-3 py-2 text-sm"
                        ></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Début</label>
                            <input
                                v-model="activityForm.start_at"
                                type="datetime-local"
                                class="w-full border rounded-lg px-3 py-2 text-sm"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fin</label>
                            <input
                                v-model="activityForm.end_at"
                                type="datetime-local"
                                class="w-full border rounded-lg px-3 py-2 text-sm"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Lieu</label>
                            <input
                                v-model="activityForm.location"
                                type="text"
                                class="w-full border rounded-lg px-3 py-2 text-sm"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Coût</label>
                            <input
                                v-model="activityForm.cost"
                                type="number"
                                step="0.01"
                                class="w-full border rounded-lg px-3 py-2 text-sm"
                            />
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-3">
                        <button
                            v-if="editingActivityId"
                            type="button"
                            @click="cancelEdit"
                            class="px-3 py-1 text-sm bg-gray-200 rounded-lg hover:bg-gray-300"
                        >
                            Annuler
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-1.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                        >
                            {{ editingActivityId ? 'Enregistrer' : 'Ajouter' }}
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</template>
