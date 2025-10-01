<!-- resources/js/Pages/Trips/Details.vue -->
<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { watch, computed } from 'vue'
import TripCreationProgress from "@/Components/Trip/TripCreationProgress.vue";
import TripForm from '@/Components/TripForm.vue'

const props = defineProps({
    destination: { type: String, default: '' },
    start: { type: String, default: '' },
})

const page = usePage()
const pageDest = computed(() => page.props?.destination ?? null)

// Destination récupérée depuis Inertia ou props
const dest = computed(() => pageDest.value ?? props.destination ?? '')

// Initialisation du formulaire
const form = useForm({
    title: dest.value ? `Voyage à ${dest.value}` : '',
    description: '',
    image: '',
    start_date: '',
    end_date: '',
    budget: '',
    currency: 'EUR',
    transport_mode: 'car',
    is_public: false,
})

// Met à jour le titre si la destination arrive après le montage
watch(dest, (d) => {
    if (!form.title && d) {
        form.title = `Voyage à ${d}`
    }
})

function submit () {
    form.post(route('trips.details.store'), { preserveScroll: true })
}
</script>

<template>
    <Head title="Complétez les infos de votre voyage" />

    <div class="max-w-3xl mx-auto py-10 px-4 space-y-10">
        <TripCreationProgress :current-step="3" />

        <div>
            <h1 class="text-2xl font-bold text-gray-900">Détails du voyage</h1>
            <p class="text-sm text-gray-500 mt-1">
                Complétez les informations de votre aventure.
            </p>
        </div>

        <TripForm
            :form="form"
            :on-submit="submit"
            :destination="dest"
            submit-label="Créer le voyage"
        />
    </div>
</template>
