<script setup>
import { computed } from 'vue'

const props = defineProps({
    type: { type: String, default: 'button' }, // "button" | "submit" | "reset"
    variant: { type: String, default: 'primary' }, // "primary" | "secondary" | "danger"
    disabled: { type: Boolean, default: false },
    loading: { type: Boolean, default: false },
})

const baseClasses = `inline-flex items-center justify-center font-semibold rounded-lg
                     px-6 py-2 transition focus:outline-none focus:ring-2 focus:ring-offset-2`

const variantClasses = computed(() => {
    switch (props.variant) {
        case 'secondary':
            return 'bg-gray-200 text-gray-800 hover:bg-gray-300 focus:ring-gray-400'
        case 'danger':
            return 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500'
        default: // bleu par défaut
            return 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500'
    }
})

const disabledClasses = 'opacity-50 cursor-not-allowed'
</script>

<template>
    <button
        :type="type"
        :disabled="disabled || loading"
        :class="[baseClasses, variantClasses, (disabled || loading) ? disabledClasses : '']"
    >
        <span v-if="loading" class="animate-spin mr-2 h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
        <slot />
    </button>
</template>
