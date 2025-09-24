<script setup>
import { ref } from 'vue'

const props = defineProps({
    trip: { type: Object, required: true }
})

const expanded = ref(false)
</script>

<template>
    <div class="bg-white rounded-2xl shadow ring-1 ring-gray-100 overflow-hidden">
        <!-- En-tête cliquable -->
        <button
            @click="expanded = !expanded"
            class="w-full flex items-center justify-between px-6 py-4 text-left"
        >
            <div>
                <h3 class="text-lg font-semibold">{{ trip.title }}</h3>
                <p class="text-sm text-gray-600">
                    📅 {{ trip.start_date }} → {{ trip.end_date }}
                    <span v-if="trip.budget"> · 💶 {{ trip.budget }} {{ trip.currency }}</span>
                </p>
            </div>
            <svg
                :class="expanded ? 'rotate-180' : ''"
                class="w-5 h-5 text-gray-500 transition-transform"
                fill="none" stroke="currentColor" viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <!-- Zone extensible -->
        <transition name="fade">
            <div v-if="expanded" class="px-6 pb-6 text-sm text-gray-700">
                <p v-if="trip.description" class="whitespace-pre-line">{{ trip.description }}</p>
                <p v-else class="text-gray-400">Aucune description pour ce voyage.</p>
            </div>
        </transition>
    </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: all 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
    transform: translateY(-5px);
}
</style>
