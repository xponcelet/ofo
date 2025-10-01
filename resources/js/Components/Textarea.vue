<script setup>
const props = defineProps({
    modelValue: { type: String, default: '' },
    label: { type: String, required: true },
    required: { type: Boolean, default: false },
    error: { type: String, default: '' },
    placeholder: { type: String, default: '' },
    rows: { type: Number, default: 4 },
})

// ✅ permet de propager tous les attributs HTML (maxlength, minlength, disabled, etc.)
defineOptions({ inheritAttrs: false })

const emit = defineEmits(['update:modelValue'])
</script>

<template>
    <div class="w-full">
        <!-- Label -->
        <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <!-- Textarea -->
        <textarea
            v-bind="$attrs"
        :value="modelValue"
        :rows="rows"
        :placeholder="placeholder"
        @input="emit('update:modelValue', $event.target.value)"
        class="w-full rounded-xl border border-gray-300 px-3 py-2
        focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500
        shadow-sm transition bg-white resize-none"
        ></textarea>

        <!-- Message d'erreur -->
        <p v-if="error" class="text-red-500 text-sm mt-1">{{ error }}</p>
    </div>
</template>
