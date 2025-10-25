<script setup>
import RootLayout from "@/Layouts/RootLayout.vue"
import { Head, router } from "@inertiajs/vue3"
import { computed, ref, watch } from "vue"

defineOptions({ layout: RootLayout })

const props = defineProps({
    trip: Object,
    steps: Array,
})

/* ---------- Utils ---------- */
const today = new Date().toISOString().slice(0, 10)
const addDays = (dateStr, n) => {
    const d = new Date(dateStr); d.setDate(d.getDate() + Number(n || 0))
    return d.toISOString().slice(0, 10)
}

/* ---------- Steps triées ---------- */
const sortedSteps = computed(() =>
    [...props.steps].sort((a, b) => (a.order ?? 0) - (b.order ?? 0))
)

/* 👉 vrai départ seulement si la 1ʳᵉ étape n’est PAS une destination */
const hasDeparture = computed(
    () => sortedSteps.value.length > 0 && !sortedSteps.value[0].is_destination
)

/* ---------- Dates du voyage : buffer + validation ---------- */
const pendingStart = ref(props.trip.start_date || "")
watch(() => props.trip.start_date, v => { pendingStart.value = v || "" })

const isPast = computed(() => !!pendingStart.value && pendingStart.value < today)
const startChanged = computed(() => (pendingStart.value || "") !== (props.trip.start_date || ""))
const canSaveTripStart = computed(() => !!pendingStart.value && !isPast.value && startChanged.value)

/* ---------- Nuits en live (debounced) ---------- */
const timers = new Map()
function debounce(key, fn, ms = 350) {
    if (timers.has(key)) clearTimeout(timers.get(key))
    const t = setTimeout(fn, ms); timers.set(key, t)
}
function onNightsChange(step, val) {
    const n = Math.max(0, parseInt(val ?? 0, 10) || 0)
    debounce(`nights:${step.id}`, () => {
        router.patch(
            route("steps.update.nights", step.id),
            { nights: n },
            { preserveScroll: true }
        )
    })
}

/* ---------- PRÉVISUALISATION LOCALE (anchor = pendingStart) ---------- */
const previewActive = computed(() => canSaveTripStart.value && hasDeparture.value)
const previewSteps = computed(() => {
    const base = sortedSteps.value.map(s => ({ ...s }))
    if (!previewActive.value) return base
    let prevEnd = null
    for (let i = 0; i < base.length; i++) {
        const s = base[i]
        if (i === 0) {
            s.start_date = pendingStart.value
            s.end_date = (s.nights ?? 0) !== null ? addDays(s.start_date, s.nights ?? 0) : null
            prevEnd = s.end_date
        } else {
            if (!prevEnd) { s.start_date = null; s.end_date = null; continue }
            s.start_date = prevEnd
            s.end_date = (s.nights ?? 0) !== null ? addDays(s.start_date, s.nights ?? 0) : null
            prevEnd = s.end_date
        }
    }
    return base
})

const previewTripEnd = computed(() => {
    if (!previewActive.value) return props.trip.end_date || ""
    const lastWithEnd = [...previewSteps.value].reverse().find(s => !!s.end_date)
    return lastWithEnd?.end_date || ""
})

/* ---------- Appliquer (rebuild) ---------- */
function saveTripStart() {
    if (!canSaveTripStart.value) return
    router.post(
        route("trips.schedule.rebuild", props.trip.id),
        { trip_start: pendingStart.value },
        { preserveScroll: true }
    )
}

/* ---------- Création & Suppression ---------- */
function addStepAfter(step) {
    const query = new URLSearchParams({ after: step.id }).toString()
    router.visit(`${route("trips.steps.create", props.trip.id)}?${query}`)
}
function addDeparture() {
    router.visit(route("trips.steps.create", props.trip.id) + "?at_start=1", { method: "get" })
}

// Suppression : autorisée si ≥ 2 étapes et si ce n’est pas la destination finale
function canDeleteStep() {
    return sortedSteps.value.length > 1
}
function deleteStep(step) {
    if (!canDeleteStep() || step.is_destination) return
    if (!confirm('Supprimer cette étape ?')) return
    router.delete(route('steps.destroy', step.id), { preserveScroll: true })
}
</script>

<template>
    <Head title="Étapes du voyage" />

    <div class="max-w-4xl mx-auto px-4 py-10 space-y-8">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Itinéraire du voyage</h1>
                <p class="text-sm text-gray-500">
                    Gérez le départ, les étapes et la destination dans l’ordre chronologique.
                </p>
            </div>
            <button
                @click="router.visit(route('trips.show', props.trip.id))"
                class="text-pink-600 hover:text-pink-700 text-sm font-medium flex items-center gap-1"
            >
                <span class="material-symbols-rounded text-[18px]">arrow_back</span>
                Retour
            </button>
        </div>

        <!-- Carte Dates du voyage (prévisualisation) -->
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                    <div class="text-gray-400 mt-0.5">🗓️</div>
                    <div>
                        <div class="font-medium">Dates du voyage</div>
                        <div class="text-sm text-gray-500">
                            Voyage du
                            <input
                                type="date"
                                :min="today"
                                v-model="pendingStart"
                                class="mx-1 text-sm border rounded px-2 py-1"
                                aria-describedby="trip-start-help"
                            />
                            au
                            <input
                                type="date"
                                :value="previewTripEnd || ''"
                                disabled
                                class="ml-1 text-sm border rounded px-2 py-1 bg-gray-50 text-gray-500"
                            />
                            <span
                                v-if="previewActive"
                                class="ml-2 text-xs inline-flex items-center px-2 py-0.5 rounded-full bg-pink-50 text-pink-700 border border-pink-100"
                                title="Prévisualisation non enregistrée"
                            >
                prévisualisation
              </span>
                        </div>
                        <p v-if="isPast" id="trip-start-help" class="text-xs text-red-600 mt-1">
                            La date de départ ne peut pas être dans le passé.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="relative border-l-2 border-gray-200 ml-4">
            <!-- A) aucune étape -->
            <div v-if="!sortedSteps.length" class="pl-8 pb-10">
                <button
                    @click="addDeparture"
                    class="flex items-center gap-2 px-4 py-2 border-2 border-dashed border-gray-300 rounded-lg text-gray-500 hover:border-pink-400 hover:text-pink-600 hover:bg-pink-50 transition"
                >
                    <span class="material-symbols-rounded">add_circle</span>
                    Ajouter le départ
                </button>
            </div>

            <!-- B) pas de départ -->
            <template v-else-if="!hasDeparture">
                <div class="pl-8 mb-4">
                    <button
                        @click="addDeparture"
                        class="flex items-center gap-2 px-4 py-2 border-2 border-dashed border-gray-300 rounded-lg text-gray-500 hover:border-pink-400 hover:text-pink-600 hover:bg-pink-50 transition"
                    >
                        <span class="material-symbols-rounded">add_circle</span>
                        Ajouter le départ
                    </button>
                </div>

                <div v-for="(step, i) in previewSteps" :key="step.id" class="relative pl-8 pb-10">
                    <div
                        class="absolute -left-[9px] top-2 w-4 h-4 rounded-full border-2"
                        :class="step.is_destination ? 'bg-pink-600 border-pink-600' : 'bg-white border-gray-400'"
                    />
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex flex-col sm:flex-row justify-between sm:items-center gap-4 hover:shadow-md transition">
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center gap-2">
                                <h3 class="text-gray-900 font-semibold">
                                    {{ step.is_destination ? 'Destination finale' : (step.title || `Étape ${i + 1}`) }}
                                </h3>
                                <span v-if="step.is_destination" class="material-symbols-rounded text-pink-600 text-sm">flag</span>
                            </div>
                            <div class="text-sm text-gray-600 flex items-center gap-1">
                                <span class="material-symbols-rounded text-base">location_on</span>
                                {{ step.location || 'Non défini' }}
                            </div>
                        </div>

                        <div class="flex items-center gap-3 text-sm flex-wrap">
                            <!-- Dates start → end -->
                            <div class="flex items-center gap-1 text-gray-500">
                                <span class="material-symbols-rounded text-base">calendar_month</span>
                                <span>{{ step.start_date ?? '—' }}</span>
                                <span class="mx-1">→</span>
                                <span>{{ step.end_date ?? '—' }}</span>
                            </div>

                            <div class="flex items-center gap-1">
                                <span class="material-symbols-rounded text-base text-gray-400">hotel</span>
                                <input
                                    type="number" min="0"
                                    class="w-16 text-center border-gray-300 rounded-lg shadow-sm focus:ring-pink-500 focus:border-pink-500"
                                    :value="step.nights"
                                    @input="onNightsChange(step, $event.target.value)"
                                />
                                <span class="text-gray-500">nuit<span v-if="step.nights > 1">s</span></span>
                            </div>

                            <button
                                @click="router.visit(route('steps.edit', { step: step.id }))"
                                class="text-pink-600 hover:text-pink-700 flex items-center gap-1"
                            >
                                <span class="material-symbols-rounded text-[18px]">edit</span>
                                Modifier
                            </button>

                            <button
                                class="text-red-600 hover:text-red-700 flex items-center gap-1 disabled:text-gray-300"
                                :disabled="step.is_destination || !canDeleteStep()"
                                @click="deleteStep(step)"
                                :title="step.is_destination ? 'La destination finale ne peut pas être supprimée' : 'Au moins une étape doit rester'"
                            >
                                <span class="material-symbols-rounded text-[18px]">delete</span>
                                Supprimer
                            </button>
                        </div>
                    </div>

                    <div class="pl-8 mt-3">
                        <button
                            @click="addStepAfter(step)"
                            class="flex items-center gap-2 px-3 py-1.5 border-2 border-dashed border-gray-300 rounded-lg text-gray-500 hover:border-pink-400 hover:text-pink-600 hover:bg-pink-50 transition text-sm"
                        >
                            <span class="material-symbols-rounded text-[18px]">add</span>
                            Ajouter une étape ici
                        </button>
                    </div>
                </div>
            </template>

            <!-- C) départ présent -->
            <template v-else>
                <!-- Départ -->
                <div class="relative pl-8 pb-10">
                    <div class="absolute -left-[9px] top-2 w-4 h-4 bg-pink-600 border-2 border-pink-600 rounded-full" />
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex flex-col sm:flex-row justify-between sm:items-center gap-4 hover:shadow-md transition">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="material-symbols-rounded text-pink-600 text-lg">flight_takeoff</span>
                                <h3 class="text-gray-900 font-semibold">Départ</h3>
                            </div>
                            <p class="text-gray-600 text-sm">
                                {{ previewSteps[0].location || 'Lieu non défini' }}
                            </p>
                        </div>

                        <div class="flex items-center gap-3 text-sm">
                            <!-- Dates start → end (départ) -->
                            <div class="flex items-center gap-1 text-gray-500">
                                <span class="material-symbols-rounded text-base">calendar_month</span>
                                <span>{{ previewSteps[0].start_date ?? '—' }}</span>
                                <span class="mx-1">→</span>
                                <span>{{ previewSteps[0].end_date ?? '—' }}</span>
                            </div>

                            <button
                                @click="router.visit(route('steps.edit', { step: previewSteps[0].id }))"
                                class="text-pink-600 hover:text-pink-700 flex items-center gap-1"
                            >
                                <span class="material-symbols-rounded text-[18px]">edit</span>
                                Modifier
                            </button>

                            <button
                                class="text-red-600 hover:text-red-700 flex items-center gap-1 disabled:text-gray-300"
                                :disabled="previewSteps[0].is_destination || !canDeleteStep()"
                                @click="deleteStep(previewSteps[0])"
                                :title="previewSteps[0].is_destination ? 'La destination finale ne peut pas être supprimée' : 'Au moins une étape doit rester'"
                            >
                                <span class="material-symbols-rounded text-[18px]">delete</span>
                                Supprimer
                            </button>
                        </div>
                    </div>

                    <div class="pl-8 mt-3">
                        <button
                            @click="addStepAfter(previewSteps[0])"
                            class="flex items-center gap-2 px-3 py-1.5 border-2 border-dashed border-gray-300 rounded-lg text-gray-500 hover:border-pink-400 hover:text-pink-600 hover:bg-pink-50 transition text-sm"
                        >
                            <span class="material-symbols-rounded text-[18px]">add</span>
                            Ajouter une étape ici
                        </button>
                    </div>
                </div>

                <!-- Étapes suivantes -->
                <div v-for="(step, i) in previewSteps.slice(1)" :key="step.id" class="relative pl-8 pb-10">
                    <div
                        class="absolute -left-[9px] top-2 w-4 h-4 rounded-full border-2"
                        :class="step.is_destination ? 'bg-pink-600 border-pink-600' : 'bg-white border-gray-400'"
                    />
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex flex-col sm:flex-row justify-between sm:items-center gap-4 hover:shadow-md transition">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="material-symbols-rounded text-gray-500 text-lg">location_on</span>
                                <h3 class="text-gray-900 font-semibold">
                                    {{ step.is_destination ? 'Destination finale' : (step.title || `Étape ${i + 2}`) }}
                                </h3>
                            </div>
                            <p class="text-gray-600 text-sm">{{ step.location }}</p>
                        </div>

                        <div class="flex items-center gap-3 text-sm">
                            <!-- Dates start → end -->
                            <div class="flex items-center gap-1 text-gray-500">
                                <span class="material-symbols-rounded text-base">calendar_month</span>
                                <span>{{ step.start_date ?? '—' }}</span>
                                <span class="mx-1">→</span>
                                <span>{{ step.end_date ?? '—' }}</span>
                            </div>

                            <div class="flex items-center gap-1">
                                <span class="material-symbols-rounded text-base text-gray-400">hotel</span>
                                <input
                                    type="number" min="0"
                                    class="w-16 text-center border-gray-300 rounded-lg shadow-sm focus:ring-pink-500 focus:border-pink-500"
                                    :value="step.nights"
                                    @input="onNightsChange(step, $event.target.value)"
                                />
                                <span class="text-gray-500">
                  nuit<span v-if="step.nights > 1">s</span>
                </span>
                            </div>

                            <button
                                @click="router.visit(route('steps.edit', { step: step.id }))"
                                class="text-pink-600 hover:text-pink-700 flex items-center gap-1"
                            >
                                <span class="material-symbols-rounded text-[18px]">edit</span>
                                Modifier
                            </button>

                            <button
                                class="text-red-600 hover:text-red-700 flex items-center gap-1 disabled:text-gray-300"
                                :disabled="step.is_destination || !canDeleteStep()"
                                @click="deleteStep(step)"
                                :title="step.is_destination ? 'La destination finale ne peut pas être supprimée' : 'Au moins une étape doit rester'"
                            >
                                <span class="material-symbols-rounded text-[18px]">delete</span>
                                Supprimer
                            </button>
                        </div>
                    </div>

                    <div class="pl-8 mt-3">
                        <button
                            @click="addStepAfter(step)"
                            class="flex items-center gap-2 px-3 py-1.5 border-2 border-dashed border-gray-300 rounded-lg text-gray-500 hover:border-pink-400 hover:text-pink-600 hover:bg-pink-50 transition text-sm"
                        >
                            <span class="material-symbols-rounded text-[18px]">add</span>
                            Ajouter une étape ici
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <!-- Bouton global -->
        <div class="pt-2">
            <button
                class="w-full sm:w-auto bg-pink-600 hover:bg-pink-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition disabled:bg-gray-200 disabled:text-gray-500"
                :disabled="!canSaveTripStart"
                @click="saveTripStart"
            >
                Enregistrer les modifications
            </button>
            <p v-if="previewActive" class="text-xs text-gray-500 mt-1">
                Prévisualisation affichée : cliquez sur “Enregistrer les modifications” pour appliquer côté serveur.
            </p>
        </div>
    </div>
</template>
