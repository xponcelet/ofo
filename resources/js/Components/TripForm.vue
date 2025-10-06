<script setup>
import Input from '@/Components/Input.vue'
import Textarea from '@/Components/Textarea.vue'
import Select from '@/Components/Select.vue'
import Checkbox from '@/Components/Checkbox.vue'
import Button from '@/Components/Button.vue'
import { watch, onMounted } from 'vue'

const props = defineProps({
    form: Object,
    onSubmit: Function,
    submitLabel: { type: String, default: 'Valider' },
    destination: { type: String, default: '' },
})

// Pré-remplit le titre si vide au montage
onMounted(() => {
    if (!props.form.title && props.destination) {
        props.form.title = `Voyage à ${props.destination}`
    }
})

// --- Synchronisation dates <-> nuits ---
// Calcul end_date si nights change
watch(
    () => [props.form.start_date, props.form.nights],
    ([start, nights]) => {
        if (start && nights !== '' && nights >= 0) {
            const d = new Date(start)
            d.setDate(d.getDate() + Number(nights))
            const newEnd = d.toISOString().slice(0, 10)
            if (props.form.end_date !== newEnd) {
                props.form.end_date = newEnd
            }
        }
    }
)
// Calcul nights si end_date change
watch(
    () => [props.form.start_date, props.form.end_date],
    ([start, end]) => {
        if (start && end) {
            const d1 = new Date(start)
            const d2 = new Date(end)
            if (d2 >= d1) {
                const diffDays = Math.round(
                    (d2.getTime() - d1.getTime()) / (1000 * 60 * 60 * 24)
                )
                if (props.form.nights !== diffDays) {
                    props.form.nights = diffDays
                }
            }
        }
    }
)
</script>

<template>
    <div class="max-w-3xl mx-auto">
        <form @submit.prevent="onSubmit" class="space-y-10">
            <!-- =======================
                 Infos générales
            ======================= -->
            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Informations générales</h2>

                <Input
                    v-model="form.title"
                    label="Titre du voyage"
                    required
                    :placeholder="destination ? `Voyage à ${destination}` : 'Ex. Road trip au Portugal'"
                    :error="form.errors.title"
                />

                <Textarea
                    v-model="form.description"
                    label="Description"
                    placeholder="Racontez un peu ce que vous comptez faire..."
                    :error="form.errors.description"
                />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <Input
                        v-model="form.budget"
                        label="Budget"
                        type="number"
                        min="0"
                        step="0.01"
                        placeholder="Ex. 1200"
                        :error="form.errors.budget"
                    />

                    <Select
                        v-model="form.currency"
                        label="Devise"
                        :options="[
                            { value: 'EUR', label: 'Euro (€)' },
                            { value: 'USD', label: 'Dollar ($)' },
                        ]"
                        :error="form.errors.currency"
                    />
                </div>

                <Checkbox
                    v-model="form.is_public"
                    label="Rendre ce voyage public"
                    :error="form.errors.is_public"
                />
            </div>

            <!-- =======================
                 Logistique destination
            ======================= -->
            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Logistique</h2>
                <p class="text-sm text-gray-500 mb-4">
                    Ces informations concernent la destination choisie.
                    Vous pourrez les modifier plus tard.
                </p>

                <Input
                    v-model="form.start_date"
                    label="Date d’arrivée"
                    type="date"
                    :min="new Date().toISOString().slice(0, 10)"
                    :error="form.errors.start_date"
                />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <Input
                            v-model="form.nights"
                            label="Nombre de nuits"
                            type="number"
                            min="0"
                            step="1"
                            :error="form.errors.nights"
                        />
                        <p class="text-xs text-gray-500 mt-1">
                            La date de fin est calculée automatiquement.
                        </p>
                    </div>

                    <Input
                        v-model="form.end_date"
                        label="Date de fin"
                        type="date"
                        readonly
                    />
                </div>

                <Select
                    v-model="form.transport_mode"
                    label="Moyen de transport"
                    :options="[
                        { value: 'car', label: 'Voiture' },
                        { value: 'plane', label: 'Avion' },
                        { value: 'train', label: 'Train' },
                        { value: 'bus', label: 'Bus' },
                        { value: 'bike', label: 'Vélo' },
                        { value: 'walk', label: 'Marche' },
                        { value: 'boat', label: 'Bateau' },
                    ]"
                    :error="form.errors.transport_mode"
                />
            </div>

            <!-- =======================
                 Bouton
            ======================= -->
            <div class="flex justify-end pt-6">
                <Button
                    type="submit"
                    variant="primary"
                    :disabled="form.processing"
                    :loading="form.processing"
                >
                    {{ submitLabel }}
                </Button>
            </div>
        </form>
    </div>
</template>
