<script setup>
import { computed } from 'vue'

const props = defineProps({
    step: { type: Number, required: true },     // ex: 1, 2, 3
    total: { type: Number, default: 3 },        // nombre total d’étapes
    labels: {
        type: Array,
        default: () => ['Destination', 'Dates', 'Détails']
    }
})

const progress = computed(() => {
    return Math.round((props.step / props.total) * 100)
})
</script>

<template>
    <div class="w-full space-y-2">
        <!-- Barre de progression -->
        <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
            <div
                class="h-full bg-blue-600 transition-all duration-500"
                :style="{ width: `${progress}%` }"
            />
        </div>

        <!-- Étapes (facultatif mais sympa) -->
        <div class="flex justify-between text-xs font-medium text-gray-500">
            <template v-for="(label, index) in labels" :key="index">
                <div :class="[
                    'flex-1 text-center',
                    index + 1 === step ? 'text-blue-700 font-semibold' : '',
                ]">
                    {{ label }}
                </div>
            </template>
        </div>
    </div>
</template>
