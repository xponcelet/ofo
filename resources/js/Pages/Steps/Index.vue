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

/** 🔁 Début du drag */
function handleDragStart(index) {
    dragSrcIndex.value = index
}

/** 🧲 Pendant le drag (nécessaire pour autoriser le drop) */
function handleDragOver(e) {
    e.preventDefault()
}

/** 🎯 Dépôt : on échange les éléments */
function handleDrop(index) {
    if (dragSrcIndex.value === null) return
    const moved = localSteps.value.splice(dragSrcIndex.value, 1)[0]
    localSteps.value.splice(index, 0, moved)
    dragSrcIndex.value = null
    updateOrder()
}

/** 💾 Mise à jour de l’ordre côté backend */
function updateOrder() {
    const orderedIds = localSteps.value.map((s) => s.id)
    router.put(
        route("steps.reorder", props.trip.id),
        { order: orderedIds },
        { preserveScroll: true }
    )
}

/** 🌙 Mise à jour du nombre de nuits */
function updateNights(step) {
    router.put(route("steps.update", step.id), { nights: step.nights }, {
        preserveScroll: true,
    })
}
</script>

<template>
    <div class="max-w-5xl mx-auto py-10 px-6">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-2xl font-bold">Étapes de {{ trip.title }}</h1>
                <p class="text-sm text-gray-500">{{ localSteps.length }} étapes au total</p>
            </div>
            <Link
                :href="route('trips.show', trip.id)"
                class="text-sm text-pink-600 hover:underline"
            >
                ← Retour au voyage
            </Link>
        </div>

        <!-- 🪄 Liste réorganisable native -->
        <div
            v-if="localSteps.length"
            class="bg-white rounded-xl shadow divide-y"
        >
            <div
                v-for="(step, i) in localSteps"
                :key="step.id"
                class="flex items-center justify-between px-5 py-4 hover:bg-gray-50 transition cursor-grab"
                draggable="true"
                @dragstart="handleDragStart(i)"
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
                    <!-- 🌙 Nombre de nuits -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-500">Nuits :</span>
                        <input
                            type="number"
                            min="0"
                            v-model.number="step.nights"
                            @change="updateNights(step)"
                            class="w-16 text-sm border-gray-300 rounded-md focus:border-pink-500 focus:ring-pink-500"
                        />
                    </div>

                    <!-- ✏️ Actions -->
                    <div class="flex gap-3">
                        <Link
                            :href="route('steps.edit', step.id)"
                            class="text-pink-600 hover:underline text-sm"
                        >
                            ✏️ Modifier
                        </Link>
                        <Link
                            :href="route('steps.destroy', step.id)"
                            method="delete"
                            as="button"
                            class="text-red-600 hover:underline text-sm"
                            onclick="return confirm('Supprimer cette étape ?')"
                        >
                            🗑️ Supprimer
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <p v-else class="text-gray-500 mt-4">Aucune étape pour l’instant.</p>

        <div class="mt-8">
            <Link
                :href="route('trips.steps.create', trip.id)"
                class="inline-block bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 transition"
            >
                ➕ Ajouter une étape
            </Link>
        </div>
    </div>
</template>
