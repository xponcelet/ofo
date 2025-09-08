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

            <!-- Liste (mode lecture) -->
            <div class="bg-white rounded-2xl p-5 shadow-sm ring-1 ring-gray-100">
                <h2 class="text-lg font-semibold mb-4">📋 Activités de cette étape</h2>

                <div v-if="hasActivities" class="space-y-4">
                    <article
                        v-for="a in step.activities"
                        :key="a.id"
                        class="p-4 rounded-xl border border-gray-200 hover:border-gray-300 transition"
                    >
                        <header class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="font-medium truncate">{{ a.title }}</h3>
                                <p class="text-sm text-gray-600" v-if="a.description">{{ a.description }}</p>

                                <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-600">
                  <span v-if="a.start_at || a.end_at" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-gray-100">
                    <svg class="w-3 h-3" viewBox="0 0 24 24"><path fill="currentColor" d="M19 4h-1V2h-2v2H8V2H6v2H5a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2m0 15H5V9h14z"/></svg>
                    <span>
                      <template v-if="a.start_at && a.end_at">{{ fmtDateTime(a.start_at) }} → {{ fmtDateTime(a.end_at) }}</template>
                      <template v-else-if="a.start_at">Le {{ fmtDateTime(a.start_at) }}</template>
                    </span>
                  </span>

                                    <span v-if="a.category" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-blue-50 text-blue-700">#{{ a.category }}</span>
                                    <span v-if="a.cost != null" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700">{{ fmtMoney(a.cost, a.currency) }}</span>
                                    <a v-if="a.external_link" :href="a.external_link" target="_blank" rel="noopener" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-purple-50 text-purple-700 hover:bg-purple-100">
                                        🔗 Lien
                                    </a>
                                </div>
                            </div>

                            <div class="shrink-0 flex gap-2">
                                <Link :href="route('activities.edit', a.id)" class="px-3 py-1.5 rounded-2xl bg-blue-100 hover:bg-blue-200 text-sm">✏️ Modifier</Link>
                                <button @click="deleteActivity(a.id)" class="px-3 py-1.5 rounded-2xl bg-red-100 hover:bg-red-200 text-red-700 text-sm">🗑 Supprimer</button>
                            </div>
                        </header>
                    </article>
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
import { ref, computed } from 'vue'
import { useForm, router, Link } from '@inertiajs/vue3'
import InputError from '@/Components/InputError.vue'
import MapboxAutocomplete from '@/Components/MapboxAutocomplete.vue'
import StepPickMap from '@/Components/StepPickMap.vue'
import RootLayout from "@/Layouts/RootLayout.vue";

const props = defineProps({
    step: Object,
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
function applyLabelName({ place }) { if (place) form.location = place }
function submit() { form.put(route('steps.update', props.step.id)) }

// --- Logements ---
function deleteAccommodation(id) {
    if (confirm('Supprimer ce logement ?')) {
        router.delete(route('accommodations.destroy', id))
    }
}

// --- Activités ---
const hasActivities = computed(() => Array.isArray(props.step.activities) && props.step.activities.length > 0)

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
            newActivity.value = { title: '', description: '', start_at: '', end_at: '', external_link: '', cost: '', currency: 'EUR', category: '' }
        },
    })
}

function deleteActivity(id) {
    if (!confirm('Supprimer cette activité ?')) return
    router.delete(route('activities.destroy', id), { preserveScroll: true })
}

// Format helpers
function fmtDateTime(dt) {
    if (!dt) return null
    try {
        const d = new Date(dt)
        return new Intl.DateTimeFormat(undefined, {
            year: 'numeric', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit'
        }).format(d)
    } catch { return null }
}
function fmtMoney(amount, currency) {
    if (amount == null || amount === '') return null
    try {
        return new Intl.NumberFormat(undefined, { style: 'currency', currency: (currency || 'EUR') }).format(Number(amount))
    } catch { return `${amount} ${currency || ''}`.trim() }
}
</script>

<style scoped>
</style>
