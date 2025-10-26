<script setup>
import { Head, router, useForm } from '@inertiajs/vue3'
import { computed, nextTick, ref, watch } from 'vue'

const props = defineProps({
    trip: { type: Object, required: true },
    items: { type: Array, default: () => [] },
    title: { type: String, default: 'Checklist du voyage' },
    width: { type: String, default: 'max-w-2xl' },
    dense: { type: Boolean, default: true },
})

const list = ref(props.items.map(i => ({ ...i, _editing: false })))
watch(() => props.items, nv => { list.value = nv.map(i => ({ ...i, _editing: false })) })

const addForm = useForm({ label: '' })
const addItem = () => {
    const label = addForm.label.trim()
    if (!label) return
    addForm.post(route('trips.checklist-items.store', props.trip.id), {
        onSuccess: () => addForm.reset('label'),
        preserveScroll: true,
    })
}

const toggleItem = async (item) => {
    const prev = item.is_checked
    item.is_checked = !prev
    try {
        await router.patch(route('checklist-items.toggle', item.id), {}, { preserveScroll: true, preserveState: true })
    } catch { item.is_checked = prev }
}

const deleting = ref(null)
const deleteItem = async (item) => {
    deleting.value = item.id
    const idx = list.value.findIndex(i => i.id === item.id)
    const backup = list.value[idx]
    list.value.splice(idx, 1)
    try {
        await router.delete(route('checklist-items.destroy', item.id), { preserveScroll: true })
    } catch {
        list.value.splice(idx, 0, backup)
    } finally { deleting.value = null }
}

const startEdit = async (item) => { item._editing = true; await nextTick(); document.getElementById(`edit-input-${item.id}`)?.focus() }
const saveEdit = async (item, ev) => {
    const newLabel = (ev?.target?.value ?? '').trim()
    if (!newLabel || newLabel === item.label) { item._editing = false; return }
    const prev = item.label
    item.label = newLabel; item._editing = false
    try { await router.put(route('checklist-items.update', item.id), { label: newLabel }, { preserveScroll: true }) }
    catch { item.label = prev }
}
const cancelEdit = (item) => { item._editing = false }

/* Drag & Drop */
const draggingId = ref(null)
const onDragStart = (e, item) => { draggingId.value = item.id; e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', String(item.id)) }
const onDragOver  = (e) => { e.preventDefault(); e.dataTransfer.dropEffect = 'move' }
const onDrop = async (e, targetItem) => {
    e.preventDefault()
    const sourceId = Number(e.dataTransfer.getData('text/plain'))
    if (!sourceId || sourceId === targetItem.id) return
    const srcIdx = list.value.findIndex(i => i.id === sourceId)
    const dstIdx = list.value.findIndex(i => i.id === targetItem.id)
    if (srcIdx === -1 || dstIdx === -1) return
    const moved = list.value.splice(srcIdx, 1)[0]
    list.value.splice(dstIdx, 0, moved)
    draggingId.value = null
    const payload = { items: list.value.map((it, idx) => ({ id: it.id, order: idx })) }
    try {
        await router.post(route('trips.checklist-items.reorder', props.trip.id), payload, { preserveScroll: true, preserveState: true })
    } catch {
        router.reload({ only: ['items'], preserveScroll: true })
    }
}

const confirmAndDelete = async (item) => {
    if (deleting.value) return
    if (!window.confirm(`Supprimer « ${item.label} » ?`)) return
    await deleteItem(item)
}

/* Indicators */
const remaining   = computed(() => list.value.filter(i => !i.is_checked).length)
const progressPct = computed(() => {
    const total = list.value.length || 1
    const done  = list.value.filter(i => i.is_checked).length
    return Math.round((done / total) * 100)
})

/* Compact classes */
const padY     = computed(() => props.dense ? 'py-2' : 'py-3')
const itemPad  = computed(() => props.dense ? 'px-3 py-2' : 'px-4 py-3')
const textSize = computed(() => props.dense ? 'text-sm' : 'text-base')
</script>

<template>
    <section class="w-full">
        <Head :title="`${title} · ${trip.title ?? 'Voyage'}`" />
        <div :class="['mx-auto w-full', props.width, 'px-2 sm:px-0']">

            <!-- 🏷️ Header -->
            <div class="flex items-end justify-between gap-2 mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-1">
                        <span class="material-symbols-rounded text-pink-600 text-[20px]">checklist</span>
                        {{ title }}
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Organise ton voyage sans rien oublier</p>
                </div>
                <div class="text-xs sm:text-sm text-gray-600">
                    <span class="font-medium">{{ remaining }}</span> restant{{ remaining > 1 ? 's' : '' }}
                </div>
            </div>

            <!-- 📊 Progress bar -->
            <div class="mb-5 rounded-xl bg-white shadow-sm ring-1 ring-gray-200 p-4">
                <div class="flex items-center justify-between text-xs text-gray-600 mb-2">
                    <span>Progression générale</span>
                    <span class="font-medium text-gray-800">{{ progressPct }}%</span>
                </div>
                <div class="h-2 w-full rounded-full bg-gray-100 overflow-hidden">
                    <div class="h-2 rounded-full bg-gradient-to-r from-pink-400 via-pink-500 to-pink-600 transition-all"
                         :style="{ width: progressPct + '%' }"></div>
                </div>
            </div>

            <!-- 📋 Liste -->
            <div v-if="list.length === 0"
                 class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200 p-6 text-center text-gray-600">
                Ta checklist est vide. Ajoute tes premiers éléments (ex. Passeport, Trousse de toilette, Chargeurs…)
            </div>

            <ul v-else class="space-y-2 mb-6">
                <li v-for="item in list" :key="item.id"
                    class="group rounded-xl bg-white shadow-sm ring-1 ring-gray-200 hover:ring-pink-200 transition"
                    draggable="true"
                    @dragstart="onDragStart($event, item)"
                    @dragover="onDragOver"
                    @drop="onDrop($event, item)"
                    :class="draggingId === item.id ? 'opacity-60' : 'opacity-100'">
                    <div :class="['flex items-center gap-3', itemPad]">
                        <!-- drag handle -->
                        <div class="cursor-grab text-gray-400 hover:text-gray-500" title="Déplacer">
                            <span class="material-symbols-rounded text-[20px]">drag_indicator</span>
                        </div>

                        <!-- checkbox + label -->
                        <label class="flex items-center gap-3 flex-1 cursor-pointer">
                            <input
                                type="checkbox"
                                :checked="item.is_checked"
                                @change="toggleItem(item)"
                                class="h-5 w-5 rounded border-gray-300 text-pink-600 focus:ring-pink-600"
                            />
                            <div class="flex-1">
                                <div v-if="!item._editing"
                                     :class="['min-h-[26px] leading-7', textSize, item.is_checked ? 'line-through text-gray-500' : 'text-gray-900']"
                                     @dblclick="startEdit(item)">
                                    {{ item.label }}
                                </div>
                                <input v-else
                                       :id="`edit-input-${item.id}`"
                                       type="text"
                                       :value="item.label"
                                       :class="['w-full rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-pink-600 px-2', props.dense ? 'py-1' : 'py-1.5', textSize]"
                                       @keydown.enter.prevent="saveEdit(item, $event)"
                                       @keydown.esc.prevent="cancelEdit(item)"
                                       @blur="saveEdit(item, $event)" />
                            </div>
                        </label>

                        <!-- delete -->
                        <button type="button"
                                @click="confirmAndDelete(item)"
                                :disabled="deleting === item.id"
                                class="text-xs sm:text-sm text-red-500 hover:text-red-700 hover:bg-red-50 rounded-md px-2 py-1 transition disabled:opacity-50">
                            Supprimer
                        </button>
                    </div>
                </li>
            </ul>

            <!-- ➕ Ajouter (en bas) -->
            <form @submit.prevent="addItem" class="flex gap-2 sticky bottom-0 bg-white/80 backdrop-blur-sm p-3 rounded-xl shadow-sm border border-gray-200">
                <input
                    v-model="addForm.label"
                    type="text"
                    placeholder="Ajouter un élément (ex. Passeport)"
                    class="flex-1 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-pink-600 px-3 py-2 text-sm"
                />
                <button
                    type="submit"
                    class="rounded-lg bg-pink-600 text-white hover:bg-pink-700 active:bg-pink-800 transition px-4 py-2 text-sm font-medium flex items-center gap-1"
                >
                    <span class="material-symbols-rounded text-[18px]">add</span>
                    Ajouter
                </button>
            </form>
            <div v-if="addForm.errors.label" class="text-xs text-red-600 mt-2">{{ addForm.errors.label }}</div>
        </div>
    </section>
</template>

<style scoped>
.material-symbols-rounded {
    font-variation-settings:
        'FILL' 1,
        'wght' 400,
        'GRAD' 0,
        'opsz' 24;
}
</style>
