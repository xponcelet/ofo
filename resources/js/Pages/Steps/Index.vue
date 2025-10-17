<script setup>
import RootLayout from "@/Layouts/RootLayout.vue"
import { Link, router } from "@inertiajs/vue3"
import { ref } from "vue"

defineOptions({ layout: RootLayout })

const props = defineProps({
    trip: Object,
    steps: Array,
})

const localSteps = ref([...props.steps])
const dragSrcIndex = ref(null)
const draggingId = ref(null)
let nightsTimer = null

function handleDragStart(index, step) {
    dragSrcIndex.value = index
    draggingId.value = step.id
}
function handleDragOver(e) { e.preventDefault() }
function handleDrop(index) {
    if (dragSrcIndex.value === null) return
    const moved = localSteps.value.splice(dragSrcIndex.value, 1)[0]
    localSteps.value.splice(index, 0, moved)
    dragSrcIndex.value = null
    draggingId.value = null
    updateOrder()
}
function updateOrder() {
    const orderedIds = localSteps.value.map(s => s.id)
    router.put(route("steps.reorder", props.trip.id), { order: orderedIds }, { preserveScroll: true })
}

/** 🌙 Debounce + PATCH dédié */
function updateNights(step) {
    clearTimeout(nightsTimer)
    nightsTimer = setTimeout(() => {
        router.patch(route("steps.update.nights", step.id), { nights: step.nights }, {
            preserveScroll: true,
        })
    }, 400)
}
</script>

<template>
    <!-- ... en-tête identique ... -->

    <div v-if="localSteps.length" class="bg-white rounded-xl shadow divide-y">
        <transition-group name="fade-move" tag="div">
            <div
                v-for="(step, i) in localSteps"
                :key="step.id"
                class="flex items-center justify-between px-5 py-4 transition-all duration-200 ease-in-out cursor-grab"
                :class="{
          'bg-pink-50 scale-[1.01] shadow-md': draggingId === step.id,
          'hover:bg-gray-50': draggingId !== step.id
        }"
                draggable="true"
                @dragstart="handleDragStart(i, step)"
                @dragover="handleDragOver"
                @drop="handleDrop(i)"
            >
                <div class="flex items-center gap-3">
                    <div class="text-gray-400 select-none">☰</div>
                    <div>
                        <h2 class="font-medium">
                            {{ i + 1 }}. {{ step.title || step.location || 'Étape sans nom' }}
                        </h2>
                        <p class="text-sm text-gray-500">
                            {{ step.start_date || '—' }} → {{ step.end_date || '—' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-6">
                    <!-- 🌙 Nuits -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-500">Nuits :</span>
                        <input
                            type="number"
                            min="0"
                            v-model.number="step.nights"
                            @input="updateNights(step)"
                            class="w-16 text-sm border-gray-300 rounded-md focus:border-pink-500 focus:ring-pink-500"
                        />
                    </div>

                    <div class="flex gap-3">
                        <Link :href="route('steps.edit', step.id)" class="text-pink-600 hover:underline text-sm">
                            ✏️ Modifier
                        </Link>
                        <Link
                            :href="route('steps.destroy', step.id)"
                            method="delete" as="button"
                            class="text-red-600 hover:underline text-sm"
                            onclick="return confirm('Supprimer cette étape ?')"
                        >
                            🗑️ Supprimer
                        </Link>
                    </div>
                </div>
            </div>
        </transition-group>
    </div>

    <!-- ... footer identique ... -->
</template>

<style scoped>
.fade-move-move,
.fade-move-enter-active,
.fade-move-leave-active { transition: all 0.25s ease; }
.fade-move-enter-from,
.fade-move-leave-to { opacity: 0; transform: translateY(10px); }
</style>
