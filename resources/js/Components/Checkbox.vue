<script setup>
const props = defineProps({
    modelValue: { type: Boolean, default: false },
    label: { type: String, required: true },
    error: { type: String, default: '' },
})

// ✅ permet de propager tous les attributs HTML natifs (disabled, required…)
defineOptions({ inheritAttrs: false })

const emit = defineEmits(['update:modelValue'])
</script>

<template>
    <div class="flex items-start space-x-2">
        <!-- Checkbox -->
        <input
            v-bind="$attrs"
        type="checkbox"
        :checked="modelValue"
        @change="emit('update:modelValue', $event.target.checked)"
        class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500"
        />

        <!-- Label -->
        <label class="text-sm text-gray-700 cursor-pointer select-none">
            {{ label }}
        </label>

        <!-- Message d'erreur -->
        <p v-if="error" class="text-red-500 text-sm mt-1 w-full">{{ error }}</p>
    </div>
</template>
