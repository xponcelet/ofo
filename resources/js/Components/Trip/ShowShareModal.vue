<script setup>
import { ref, computed } from 'vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
    show: { type: Boolean, required: true },
    trip: { type: Object, required: true },
})

const emit = defineEmits(['close'])

const copied = ref(false)

// 🔗 URL publique du voyage
const publicUrl = computed(() => `${window.location.origin}/voyages/${props.trip.slug ?? props.trip.id}`)

function copyLink() {
    navigator.clipboard.writeText(publicUrl.value)
    copied.value = true
    setTimeout(() => (copied.value = false), 2000)
}
</script>

<template>
    <Modal :show="show" @close="emit('close')" max-width="md">
        <div class="p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">Partager le voyage</h2>

            <p class="text-sm text-gray-600 mb-3">
                Voici le lien public de ton voyage :
            </p>

            <div class="flex items-center gap-2">
                <input
                    type="text"
                    :value="publicUrl"
                    readonly
                    class="flex-1 px-3 py-2 border rounded-lg text-sm text-gray-700 bg-gray-50 focus:outline-none"
                />
                <button
                    @click="copyLink"
                    class="px-3 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700 transition"
                >
                    {{ copied ? '✅ Copié !' : '📋 Copier' }}
                </button>
            </div>

            <div class="mt-6 flex justify-end">
                <button
                    @click="emit('close')"
                    class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition"
                >
                    Fermer
                </button>
            </div>
        </div>
    </Modal>
</template>
