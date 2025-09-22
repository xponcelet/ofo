<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    is_premium: Boolean,
    current_period_end: String, // ISO
    invoices: Array,
    stripe_public_key: String,
    price_id: String,
})

const nextRenewal = computed(() =>
    props.current_period_end
        ? new Date(props.current_period_end).toLocaleString()
        : null
)

function goCheckout() {
    router.post(route('billing.checkout'))
}

function openPortal() {
    router.post(route('billing.portal'))
}

function cancelSub() {
    if (confirm('Annuler votre abonnement ? Il restera actif jusqu’à la fin de la période.')) {
        router.post(route('billing.cancel'))
    }
}
</script>

<template>
    <Head title="Facturation" />

    <div class="max-w-3xl mx-auto p-6 space-y-6">
        <h1 class="text-2xl font-semibold">Facturation</h1>

        <div class="rounded-xl border p-4 space-y-3">
            <p>
                Statut :
                <span v-if="is_premium" class="font-medium">Premium ✅</span>
                <span v-else class="font-medium">Gratuit</span>
            </p>

            <p v-if="is_premium && nextRenewal">
                Renouvellement le : <strong>{{ nextRenewal }}</strong>
            </p>

            <div class="flex gap-3">
                <button
                    v-if="!is_premium"
                    @click="goCheckout"
                    class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700"
                >
                    Passer Premium
                </button>

                <button
                    v-if="is_premium"
                    @click="openPortal"
                    class="inline-flex items-center px-4 py-2 rounded-lg border hover:bg-gray-50"
                >
                    Gérer mon abonnement
                </button>

                <button
                    v-if="is_premium"
                    @click="cancelSub"
                    class="inline-flex items-center px-4 py-2 rounded-lg border hover:bg-gray-50"
                >
                    Annuler (fin de période)
                </button>
            </div>
        </div>

        <div class="rounded-xl border p-4">
            <h2 class="font-medium mb-3">Mes factures</h2>
            <div v-if="invoices?.length" class="space-y-2">
                <div v-for="inv in invoices" :key="inv.id" class="flex items-center justify-between">
                    <div>
                        <div class="font-medium">#{{ inv.number }}</div>
                        <div class="text-sm text-gray-600">
                            {{ new Date(inv.date).toLocaleString() }} — {{ inv.total }}
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a v-if="inv.hosted_invoice_url" :href="inv.hosted_invoice_url" target="_blank" class="underline">Voir</a>
                        <a v-if="inv.pdf" :href="inv.pdf" target="_blank" class="underline">PDF</a>
                    </div>
                </div>
            </div>
            <p v-else class="text-sm text-gray-600">Aucune facture.</p>
        </div>
    </div>
</template>
