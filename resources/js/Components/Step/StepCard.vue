<script setup>
import { Link, router } from "@inertiajs/vue3"
import { computed } from "vue"

const props = defineProps({
    step: { type: Object, required: true },
    tripId: { type: [Number, String], required: true },
})

// --- Données formatées ---
const formattedDates = computed(() => {
    if (!props.step.start_date) return null
    const start = new Date(props.step.start_date)
    const end = props.step.end_date ? new Date(props.step.end_date) : start
    const opts = { day: "2-digit", month: "short" }
    const startStr = start.toLocaleDateString("fr-FR", opts)
    const endStr = end.toLocaleDateString("fr-FR", opts)
    return startStr === endStr ? startStr : `${startStr} → ${endStr}`
})

const handleDelete = () => {
    if (confirm("Supprimer cette étape ?")) {
        router.delete(route("steps.destroy", { step: props.step.id }))
    }
}
</script>

<template>
    <div
        class="bg-white rounded-2xl shadow-sm border border-gray-200 hover:shadow-md transition-all p-5 flex flex-col justify-between"
    >
        <!-- En-tête -->
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-semibold text-gray-900 truncate">
                {{ step.title || "Étape sans titre" }}
            </h3>
            <span
                v-if="step.is_destination"
                class="material-symbols-rounded text-pink-600 text-xl"
                title="Destination finale"
            >
                flag
            </span>
        </div>

        <!-- Localisation -->
        <div class="flex items-center gap-2 text-gray-600 mb-2">
            <span class="material-symbols-rounded text-base">location_on</span>
            <span class="truncate">
                {{ step.location || "Non défini" }}
                <template v-if="step.country_code"> ({{ step.country_code }})</template>
            </span>
        </div>

        <!-- Dates -->
        <div v-if="formattedDates" class="flex items-center gap-2 text-gray-500 text-sm mb-3">
            <span class="material-symbols-rounded text-base">calendar_month</span>
            <span>{{ formattedDates }}</span>
            <span v-if="step.nights > 0" class="text-gray-400">• {{ step.nights }} nuit<span v-if="step.nights > 1">s</span></span>
        </div>

        <!-- Actions -->
        <div class="flex justify-between items-center mt-auto pt-3 border-t border-gray-100">
            <Link
                :href="route('steps.edit', { step: step.id })"
                class="inline-flex items-center gap-1 text-pink-600 hover:text-pink-700 text-sm font-medium"
            >
                <span class="material-symbols-rounded text-[18px]">edit</span>
                Modifier
            </Link>

            <button
                @click="handleDelete"
                class="inline-flex items-center gap-1 text-gray-500 hover:text-red-600 text-sm font-medium"
            >
                <span class="material-symbols-rounded text-[18px]">delete</span>
                Supprimer
            </button>
        </div>
    </div>
</template>
