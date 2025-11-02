<script setup>
import { computed, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import InputError from '@/Components/InputError.vue'
import MapboxAutocomplete from '@/Components/MapboxAutocomplete.vue'
import StepMapPreview from '@/Components/Step/StepMapPreview.vue'

const props = defineProps({
    form: { type: Object, required: true },
    onSubmit: { type: Function, required: true },
    submitLabel: { type: String, default: 'Valider' },
    suggestedStart: { type: String, default: null },
    atStart: { type: Boolean, default: false },
})

const page = usePage()
const routeStepId =
    page?.props?.ziggy?.routeParameters?.id ??
    page?.props?.routeParams?.id ??
    null

const mapboxInstanceKey = computed(() => {
    return `step-${props.form?.id ?? routeStepId ?? 'new'}`
})

/* --- Dates --- */
function formatLocalDate(d) {
    const y = d.getFullYear()
    const m = String(d.getMonth() + 1).padStart(2, '0')
    const day = String(d.getDate()).padStart(2, '0')
    return `${y}-${m}-${day}`
}
const today = formatLocalDate(new Date())

const computedEndDate = computed(() => {
    if (!props.form.start_date || props.form.nights == null) return ''
    const start = new Date(props.form.start_date)
    const end = new Date(start)
    end.setDate(start.getDate() + Number(props.form.nights || 0))
    return formatLocalDate(end)
})

const startIsPast = computed(() => {
    return !!props.form.start_date && props.form.start_date < today
})

const showSuggestedHint = computed(() => !!props.suggestedStart)
const suggestedIsUsed = computed(() => props.form.start_date === props.suggestedStart)

/* --- Erreurs par champ (form.errors + page.props.errors) --- */
const allErrors = computed(() => {
    const a = (page?.props?.errors) || {}
    const b = (props.form?.errors) || {}
    return { ...a, ...b }
})
const fieldError = (name) => {
    const e = allErrors.value?.[name]
    return Array.isArray(e) ? (e[0] ?? '') : (e ?? '')
}

/* --- Init champs par défaut --- */
onMounted(() => {
    Object.assign(props.form, {
        title: props.form.title ?? '',
        description: props.form.description ?? '',
        location: props.form.location ?? '',
        latitude: props.form.latitude ?? null,
        longitude: props.form.longitude ?? null,
        country: props.form.country ?? '',
        country_code: props.form.country_code ?? '',
        transport_mode: props.form.transport_mode ?? '',
        nights: props.form.nights ?? 0,
    })
})

/* --- Mapbox callbacks --- */
function updateCoords({ latitude, longitude }) {
    props.form.latitude = latitude
    props.form.longitude = longitude
}
function updateCountry(country) {
    props.form.country = country
}
function updateCountryCode(code) {
    props.form.country_code = code
}
</script>

<template>
    <form @submit.prevent="props.onSubmit" class="space-y-8">
        <!-- Section infos générales -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                {{ atStart ? 'Départ' : 'Informations générales' }}
            </h3>

            <!-- Titre -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Titre (optionnel)</label>
                <input
                    v-model="props.form.title"
                    type="text"
                    placeholder="Ex: Camp de base"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-pink-500 focus:border-pink-500"
                />
                <InputError :message="fieldError('title')" />
            </div>

            <!-- Lieu -->
            <div id="location-field" class="mb-1">
                <label class="block text-sm font-medium text-gray-700">Lieu principal *</label>
                <div :class="['rounded-lg', fieldError('location') ? 'ring-1 ring-red-400 ring-offset-0' : '']">
                    <MapboxAutocomplete
                        :key="mapboxInstanceKey"
                        v-model="props.form.location"
                        @update:coords="updateCoords"
                        @update:country="updateCountry"
                        @update:countryCode="updateCountryCode"
                    />
                </div>
                <InputError :message="fieldError('location')" />
            </div>

            <!-- Carte -->
            <StepMapPreview
                class="mt-4 rounded-lg border border-gray-200"
                :latitude="props.form.latitude"
                :longitude="props.form.longitude"
            />
        </div>

        <!-- Section logistique -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Logistique</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Date début -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Date de début</label>
                    <input
                        v-model="props.form.start_date"
                        :min="today"
                        type="date"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-pink-500 focus:border-pink-500"
                        aria-describedby="start-help"
                    />
                    <div class="mt-1">
                        <p v-if="startIsPast" id="start-help" class="text-xs text-red-600">
                            La date ne peut pas être dans le passé.
                        </p>
                        <p v-else-if="showSuggestedHint" class="text-xs text-gray-500">
                            Date proposée : <strong>{{ suggestedStart }}</strong>
                            <span v-if="suggestedIsUsed">(utilisée)</span>
                        </p>
                    </div>
                    <InputError :message="fieldError('start_date')" />
                </div>

                <!-- Nuits -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nombre de nuits</label>
                    <input
                        v-model.number="props.form.nights"
                        type="number"
                        min="0"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-pink-500 focus:border-pink-500"
                    />
                    <InputError :message="fieldError('nights')" />
                </div>

                <!-- Date fin -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Date de fin (automatique)</label>
                    <input
                        :value="computedEndDate"
                        :min="props.form.start_date || today"
                        type="date"
                        readonly
                        class="mt-1 block w-full bg-gray-100 rounded-lg border-gray-300 shadow-sm text-gray-600"
                    />
                </div>
            </div>

            <!-- Transport -->
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700">Moyen de transport</label>
                <select
                    v-model="props.form.transport_mode"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-pink-500 focus:border-pink-500"
                >
                    <option value="car">🚗 Voiture</option>
                    <option value="plane">🛫 Avion</option>
                    <option value="train">🚆 Train</option>
                    <option value="bus">🚌 Bus</option>
                    <option value="bike">🚲 Vélo</option>
                    <option value="walk">🚶 Marche</option>
                    <option value="boat">⛵ Bateau</option>
                </select>
                <InputError :message="fieldError('transport_mode')" />
            </div>
        </div>

        <!-- Description -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Description</h3>
            <textarea
                v-model="props.form.description"
                rows="4"
                placeholder="Ajoutez quelques notes ou une description..."
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-pink-500 focus:border-pink-500"
            />
            <InputError :message="fieldError('description')" />
        </div>

        <!-- Bouton -->
        <div class="flex justify-end">
            <button
                type="submit"
                class="inline-flex items-center px-5 py-2.5 bg-pink-600 text-white font-semibold rounded-lg shadow hover:bg-pink-700 transition disabled:opacity-60 disabled:cursor-not-allowed"
                :disabled="props.form.processing || startIsPast"
            >
                {{ props.submitLabel }}
            </button>
        </div>
    </form>
</template>
