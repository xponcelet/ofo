<script setup>
import { useForm } from "@inertiajs/vue3"
import { onMounted } from "vue"
import StepForm from "@/Components/StepForm.vue"

const props = defineProps({
    step: Object,
    trip: Object,
    allSteps: Array,
    suggested_start: {
        type: String,
        default: null,
    },
})

/* ===========================
   🧾 FORMULAIRE ÉTAPE
=========================== */
const form = useForm({
    id: props.step.id,
    title: props.step.title || "",
    description: props.step.description || "",
    location: props.step.location || "",
    latitude: props.step.latitude,
    longitude: props.step.longitude,
    start_date: props.step.start_date || "",
    end_date: props.step.end_date || "",
    country: props.step.country || "",
    country_code: props.step.country_code || "",
    transport_mode: props.step.transport_mode || "car",
    nights: props.step.nights ?? 0,
})

onMounted(() => {
    if (!form.start_date && props.suggested_start) {
        form.start_date = props.suggested_start
    }
})

function handleSubmit() {
    form.put(route("steps.update", props.step.id))
}
</script>

<template>
    <div class="max-w-4xl mx-auto py-12 px-6">
        <!-- ====== En-tête ====== -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-2">
                    ✏️ Modifier l’étape
                </h1>
                <p class="text-gray-500 mt-1">
                    Voyage : <span class="font-medium text-gray-700">{{ trip.title }}</span>
                </p>
            </div>

            <a
                :href="route('trips.show', trip.id)"
                class="inline-flex items-center gap-1 text-sm text-gray-600 hover:text-pink-600 transition"
            >
                <span class="material-symbols-rounded text-base">arrow_back</span>
                Retour au voyage
            </a>
        </div>

        <!-- ====== Formulaire ====== -->
        <div class="bg-white shadow-sm border border-gray-200 rounded-2xl p-8">
            <StepForm
                :form="form"
                :on-submit="handleSubmit"
                submit-label="Enregistrer les modifications"
                :suggested-start="suggested_start"
            />
        </div>
    </div>
</template>
