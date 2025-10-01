<script setup>
const props = defineProps({
    modelValue: { type: [String, Number], default: '' },
    label: { type: String, required: true },
    options: { type: Array, default: () => [] },
    required: { type: Boolean, default: false },
    error: { type: String, default: '' },
})

//  pour propager tous les autres attributs HTML (disabled, multiple, etc.)
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

        <!-- Select -->
        <select
            v-bind="$attrs"
        :value="modelValue"
        @change="emit('update:modelValue', $event.target.value)"
        class="w-full rounded-xl border border-gray-300 px-3 py-2
        focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500
        shadow-sm transition bg-white"
        >
        <option disabled value="">-- Choisir --</option>
        <option
            v-for="opt in options"
            :key="opt.value"
            :value="opt.value"
        >
            {{ opt.label }}
        </option>
        </select>

        <!-- Message d'erreur -->
        <p v-if="error" class="text-red-500 text-sm mt-1">{{ error }}</p>
    </div>
</template>
