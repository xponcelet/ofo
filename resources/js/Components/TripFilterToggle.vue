<script setup>
import { ref, watch, onMounted, computed } from 'vue'

const props = defineProps({
    modelValue: { type: String, default: 'active' },
})
const emit = defineEmits(['update:modelValue'])

const current = ref(props.modelValue)

watch(current, (val) => {
    emit('update:modelValue', val)
    if (typeof window !== 'undefined') {
        localStorage.setItem('trips_filter', val)
    }
})

onMounted(() => {
    const saved = localStorage.getItem('trips_filter')
    if (saved === 'active' || saved === 'finished') current.value = saved
})

const activeIndex = computed(() => (current.value === 'finished' ? 1 : 0))
</script>

<template>
    <div class="flex justify-center mb-6">
        <div
            class="relative inline-flex bg-gray-100/80 backdrop-blur rounded-2xl p-1 shadow-sm min-w-[270px] sm:min-w-[320px]"
        >
            <!-- Pastille animée -->
            <div
                class="absolute top-1 left-1 h-[calc(100%-0.5rem)] w-[calc(50%-0.5rem)] bg-white rounded-xl shadow transition-transform duration-300 ease-out"
                :style="{ transform: `translateX(${activeIndex * 100}%)` }"
            ></div>

            <!-- Bouton En cours & à venir -->
            <button
                type="button"
                @click="current = 'active'"
                :aria-pressed="current === 'active'"
                class="relative z-10 flex-1 px-4 py-2 rounded-xl text-sm font-medium transition-colors duration-200 focus:outline-none whitespace-nowrap"
                :class="current === 'active'
          ? 'text-gray-900'
          : 'text-gray-600 hover:text-gray-800'"
            >
                En cours & à venir
            </button>

            <!-- Bouton Terminés -->
            <button
                type="button"
                @click="current = 'finished'"
                :aria-pressed="current === 'finished'"
                class="relative z-10 flex-1 px-4 py-2 rounded-xl text-sm font-medium transition-colors duration-200 focus:outline-none"
                :class="current === 'finished'
          ? 'text-gray-900'
          : 'text-gray-600 hover:text-gray-800'"
            >
                Terminés
            </button>
        </div>
    </div>
</template>

<style scoped>
/* Empêche le retour à la ligne même sur petits écrans */
button {
    white-space: nowrap;
}

button:focus-visible {
    outline: 2px solid #ec4899; /* rose-500 */
    outline-offset: 2px;
}
</style>
