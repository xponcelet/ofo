<template>
    <div class="max-w-5xl mx-auto py-10 px-4 space-y-8">
        <!-- Header -->
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Modifier l’étape</h1>
                <p class="text-sm text-gray-500 mt-1">Gérez les infos, activités et logements de cette étape.</p>
            </div>
            <Link
                :href="route('trips.show', step.trip_id)"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium shadow-sm"
            >
                ← Retour au voyage
            </Link>
        </div>

        <!-- Tabs -->
        <div class="border-b">
            <nav class="-mb-px flex flex-wrap gap-4">
                <button
                    v-for="t in tabs"
                    :key="t.value"
                    type="button"
                    @click="currentTab = t.value"
                    class="px-3 py-2 text-sm font-medium border-b-2"
                    :class="currentTab === t.value
            ? 'border-blue-600 text-blue-700'
            : 'border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300'"
                >
                    {{ t.label }}
                </button>
            </nav>
        </div>

        <!-- TAB: Infos générales -->
        <form v-if="currentTab === 'infos'" @submit.prevent="submit" class="space-y-6">
            <!-- Titre -->
            <div class="bg-white rounded-2xl p-5 shadow-sm ring-1 ring-gray-100">
                <label class="block text-sm font-medium text-gray-700">Titre</label>
                <input
                    v-model="form.title"
                    type="text"
                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500"
                />
                <InputError :message="form.errors.title" />
            </div>

            <!-- Description -->
            <div class="bg-white rounded-2xl p-5 shadow-sm ring-1 ring-gray-100">
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea
                    v-model="form.description"
                    rows="4"
                    class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500"
                />
                <InputError :message="form.errors.description" />
            </div>

            <!-- Lieu + Carte -->
            <div class="bg-white rounded-2xl p-5 shadow-sm ring-1 ring-gray-100 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Lieu *</label>
                    <MapboxAutocomplete
                        :key="`step-${step.id}`"
                        v-model="form.location"
                        @update:coords="updateCoordsFromSearch"
                    />
                    <InputError :message="form.errors.location" />
                    <p class="mt-2 text-xs text-gray-500">
                        Astuce : cliquez sur un <strong>nom de ville</strong> sur la carte pour le sélectionner.
                    </p>
                </div>

                <StepPickMap
                    class="mt-2"
                    :latitude="form.latitude"
                    :longitude="form.longitude"
                    :recenter-on-update="true"
                    :label-layers="['settlement-major-label','settlement-minor-label','place-label']"
                    :click-padding-px="12"
                    @pick="updateCoordsFromMap"
                    @reverse-geocoded="applyLabelName"
                />
            </div>

            <!-- Dates -->
            <div class="bg-white rounded-2xl p-5 shadow-sm ring-1 ring-gray-100">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date de début</label>
                        <input
                            v-model="form.start_date"
                            type="date"
                            class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500"
                        />
                        <InputError :message="form.errors.start_date" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date de fin</label>
                        <input
                            v-model="form.end_date"
                            type="date"
                            class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500"
                        />
                        <InputError :message="form.errors.end_date" />
                    </div>
                </div>
            </div>

            <!-- Boutons -->
            <div class="flex justify-end gap-3">
                <Link
                    :href="route('trips.show', step.trip_id)"
                    class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200"
                >
                    Annuler
                </Link>
                <button
                    type="submit"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 disabled:opacity-60"
                    :disabled="form.processing"
                >
                    Enregistrer
                </button>
            </div>
        </form>

        <!-- TAB: Activités -->
        <div v-else-if="currentTab === 'activities'" class="space-y-6">
            <!-- Formulaire d'ajout -->
            <div class="bg-white rounded-2xl p-5 shadow-sm ring-1 ring-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">🧭 Ajouter une activité</h2>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium">Titre *</label>
                        <input v-model="newActivity.title" type="text" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500" placeholder="Ex: Visite du musée" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Catégorie</label>
                        <input v-model="newActivity.category" type="text" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500" placeholder="visit, food, transport..." />
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Début</label>
                        <input v-model="newActivity.start_at" type="datetime-local" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Fin</label>
                        <input v-model="newActivity.end_at" type="datetime-local" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium">Description</label>
                        <textarea v-model="newActivity.description" rows="3" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Lien externe</label>
                        <input v-model="newActivity.external_link" type="url" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500" placeholder="https://..." />
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Coût</label>
                        <input v-model="newActivity.cost" type="number" step="0.01" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Devise</label>
                        <input v-model="newActivity.currency" type="text" maxlength="3" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500" />
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <button @click="createActivity" class="px-4 py-2 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-medium shadow">Ajouter</button>
                </div>
            </div>

            <!-- Liste / édition -->
            <div class="bg-white rounded-2xl p-5 shadow-sm ring-1 ring-gray-100">
                <h2 class="text-lg font-semibold mb-4">📋 Activités de cette étape</h2>

                <div v-if="editableActivities.length" class="space-y-4">
                    <div
                        v-for="a in editableActivities"
                        :key="a.id ?? a._key"
                        class="p-4 rounded-xl border border-gray-200 hover:border-gray-300 transition"
                    >
                        <div class="grid md:grid-cols-4 gap-3 items-start">
                            <input v-model="a.title" class="rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500" />
                            <input v-model="a.start_at" type="datetime-local" class="rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500" />
                            <input v-model="a.end_at" type="datetime-local" class="rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500" />
                            <select v-model="a.step_id" class="rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500">
                                <option v-for="s in stepsForSelect" :key="s.id" :value="s.id">Étape #{{ s.order }} — {{ s.location }}</option>
                            </select>
                        </div>

                        <div class="mt-3 grid md:grid-cols-2 gap-3">
                            <input v-model="a.external_link" type="url" class="rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500" placeholder="Lien" />
                            <div class="flex gap-3">
                                <input v-model="a.cost" type="number" step="0.01" class="flex-1 rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500" placeholder="Coût" />
                                <input v-model="a.currency" type="text" maxlength="3" class="w-24 rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500" placeholder="EUR" />
                            </div>
                        </div>

                        <div class="mt-3">
                            <textarea v-model="a.description" rows="2" class="w-full rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500" placeholder="Notes..." />
                        </div>

                        <div class="mt-4 flex gap-2">
                            <button @click="updateActivity(a)" class="px-3 py-1.5 rounded-2xl border border-gray-300 hover:bg-gray-50">Enregistrer</button>
                            <button @click="deleteActivity(a)" class="px-3 py-1.5 rounded-2xl border border-red-300 text-red-700 hover:bg-red-50">Supprimer</button>
                        </div>
                    </div>
                </div>
                <p v-else class="text-sm text-gray-500">Aucune activité pour l’instant.</p>
            </div>
        </div>

        <!-- TAB: Logements -->
        <div v-else-if="currentTab === 'accommodations'" class="space-y-4">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-semibold">🏨 Logements liés à cette étape</h2>
                <Link
                    :href="route('steps.accommodations.create', step.id)"
                    class="text-sm bg-green-600 text-white px-3 py-1.5 rounded-2xl hover:bg-green-700 shadow"
                >
                    + Ajouter un logement
                </Link>
            </div>

            <div v-if="step.accommodations?.length" class="grid gap-4 sm:grid-cols-2">
                <div
                    v-for="accommodation in step.accommodations"
                    :key="accommodation.id"
                    class="border rounded-2xl p-4 bg-white shadow-sm ring-1 ring-gray-100"
                >
                    <h3 class="text-base font-semibold">{{ accommodation.title || 'Sans titre' }}</h3>
                    <p class="text-sm text-gray-600">{{ accommodation.location || 'Lieu inconnu' }}</p>
                    <p v-if="accommodation.start_date && accommodation.end_date" class="text-sm text-gray-500 mt-1">
                        📅 {{ accommodation.start_date }} → {{ accommodation.end_date }}
                    </p>
                    <div class="mt-3 flex gap-2">
                        <Link
                            :href="route('accommodations.edit', accommodation.id)"
                            class="text-xs px-3 py-1.5 bg-blue-100 hover:bg-blue-200 rounded-2xl"
                        >
                            ✏️ Modifier
                        </Link>
                        <button
                            @click="deleteAccommodation(accommodation.id)"
                            class="text-xs px-3 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 rounded-2xl"
                        >
                            🗑 Supprimer
                        </button>
                    </div>
                </div>
            </div>
            <p v-else class="text-sm text-gray-500">Aucun logement enregistré pour cette étape.</p>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { useForm, router, Link } from '@inertiajs/vue3'
import InputError from '@/Components/InputError.vue'
import MapboxAutocomplete from '@/Components/MapboxAutocomplete.vue'
import StepPickMap from '@/Components/StepPickMap.vue'

const props = defineProps({
    step: Object,
    // Recommandé: passer toutes les étapes du trip pour permettre la réassignation d'une activité
    // via le select. Controller: $trip->steps()->orderBy('order')->get(['id','order','location'])
    allSteps: { type: Array, default: () => [] },
})

// Tabs
const tabs = [
    { value: 'infos', label: 'Infos générales' },
    { value: 'activities', label: 'Activités' },
    { value: 'accommodations', label: 'Logements' },
]
const currentTab = ref('infos')

// --- Formulaire Step ---
const form = useForm({
    title: props.step.title ?? '',
    description: props.step.description ?? '',
    location: props.step.location ?? '',
    latitude: props.step.latitude != null ? Number(props.step.latitude) : null,
    longitude: props.step.longitude != null ? Number(props.step.longitude) : null,
    start_date: props.step.start_date ?? '',
    end_date: props.step.end_date ?? '',
})

function updateCoordsFromSearch({ latitude, longitude }) {
    form.latitude = latitude != null ? Number(latitude) : null
    form.longitude = longitude != null ? Number(longitude) : null
}
function updateCoordsFromMap({ latitude, longitude }) {
    form.latitude = latitude != null ? Number(latitude) : null
    form.longitude = longitude != null ? Number(longitude) : null
}
function applyLabelName({ place }) {
    if (place) form.location = place
}
function submit() {
    form.put(route('steps.update', props.step.id))
}

// --- Logements ---
function deleteAccommodation(id) {
    if (confirm('Supprimer ce logement ?')) {
        router.delete(route('accommodations.destroy', id))
    }
}

// --- Activités ---
// Helpers datetime → input[type=datetime-local]
function toLocalInput(dt) {
    if (!dt) return ''
    try {
        const d = new Date(dt)
        const pad = (n) => String(n).padStart(2, '0')
        return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`
    } catch { return '' }
}

// Liste éditable (copie locale pour éviter de muter props directement)
const editableActivities = ref([])
const refreshEditable = () => {
    const src = props.step.activities ?? []
    editableActivities.value = src.map(a => ({
        ...a,
        // normaliser pour les inputs
        start_at: toLocalInput(a.start_at),
        end_at: toLocalInput(a.end_at),
        step_id: a.step_id ?? props.step.id,
    }))
}
refreshEditable()
watch(() => props.step.activities, refreshEditable, { deep: true })

// Select étapes pour réassigner — fallback: au moins l'étape courante
const stepsForSelect = computed(() => props.allSteps?.length ? props.allSteps : [{ id: props.step.id, order: props.step.order, location: props.step.location }])

// Création
const newActivity = ref({
    title: '',
    description: '',
    start_at: '',
    end_at: '',
    external_link: '',
    cost: '',
    currency: 'EUR',
    category: '',
})

function createActivity() {
    if (!newActivity.value.title?.trim()) return alert('Le titre est requis.')
    router.post(route('steps.activities.store', props.step.id), newActivity.value, {
        preserveScroll: true,
        onSuccess: () => {
            // reset et recharger côté serveur (Back → Step edit)
            newActivity.value = { title: '', description: '', start_at: '', end_at: '', external_link: '', cost: '', currency: 'EUR', category: '' }
        },
    })
}

function updateActivity(a) {
    router.put(route('activities.update', a.id), {
        step_id: a.step_id,
        title: a.title,
        description: a.description,
        start_at: a.start_at,
        end_at: a.end_at,
        external_link: a.external_link,
        cost: a.cost,
        currency: a.currency,
        category: a.category,
    }, { preserveScroll: true })
}

function deleteActivity(a) {
    if (!confirm('Supprimer cette activité ?')) return
    router.delete(route('activities.destroy', a.id), { preserveScroll: true })
}
</script>

<style scoped>
/***** petites finitions *****/
</style>
