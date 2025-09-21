<script setup>
const props = defineProps({
    modelValue: { type: Boolean, default: false },
    label: { type: String, required: true },
    error: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue'])
</script>

<template>
    <div class="flex flex-col">
        <label class="inline-flex items-center gap-2">
            <input
                type="checkbox"
                :checked="modelValue"
                @change="emit('update:modelValue', $event.target.checked)"
                class="rounded border-gray-300 text-primary-600 shadow-sm
               focus:ring-primary-500 transition"
            />
            <span class="text-sm text-gray-700">{{ label }}</span>
        </label>

        <p v-if="error" class="text-red-500 text-sm mt-1">{{ error }}</p>
    </div>
</template>
