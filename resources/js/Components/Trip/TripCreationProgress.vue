<template>
    <nav aria-label="Progression du voyage" class="flex justify-center mb-8">
        <ol role="list" class="flex items-center space-x-4">
            <li v-for="(step, index) in steps" :key="step.name" class="flex items-center">
                <!-- Cercle numéroté -->
                <div
                    class="flex items-center justify-center w-8 h-8 rounded-full border-2 text-sm font-medium"
                    :class="{
            'bg-blue-600 border-blue-600 text-white': index + 1 === currentStep,
            'bg-gray-200 border-gray-200 text-gray-500': index + 1 > currentStep,
            'bg-white border-blue-600 text-blue-600': index + 1 < currentStep,
          }"
                >
                    {{ index + 1 }}
                </div>

                <!-- Label -->
                <span
                    class="ml-2 text-sm"
                    :class="{
            'text-gray-900 font-semibold': index + 1 === currentStep,
            'text-gray-400': index + 1 > currentStep,
            'text-blue-600': index + 1 < currentStep,
          }"
                >
          {{ step.label }}
        </span>

                <!-- Flèche -->
                <div v-if="index < steps.length - 1" class="mx-4 h-px w-6 bg-gray-300" />
            </li>
        </ol>
    </nav>
</template>

<script setup>
const props = defineProps({
    currentStep: {
        type: Number,
        required: true,
        validator: (value) => value >= 1 && value <= 3,
    },
})

// Étapes fixes pour l'instant
const steps = [
    { name: 'destination', label: 'Destination' },
    { name: 'start', label: 'Départ' },
    { name: 'details', label: 'Détails' },
]
</script>
