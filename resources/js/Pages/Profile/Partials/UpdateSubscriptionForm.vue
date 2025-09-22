<script setup>
import ActionSection from '@/Components/ActionSection.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'

const props = defineProps({
    user: Object,
})

function csrfToken() {
    const el = document.querySelector('meta[name=csrf-token]')
    return el ? el.getAttribute('content') : ($page.props.csrf_token ?? '')
}
</script>

<template>
    <ActionSection>
        <template #title>
            Mon abonnement
        </template>

        <template #description>
            Gérez votre abonnement Premium (paiement, renouvellement, annulation) et retrouvez vos factures.
        </template>

        <template #content>
            <!-- Flash -->
            <div v-if="$page.props.flash?.status" class="mb-4 p-3 rounded bg-green-100 text-green-800">
                {{ $page.props.flash.status }}
            </div>

            <!-- Statut -->
            <div class="text-sm text-gray-700 space-y-1">
                <p>
                    Statut actuel :
                    <span v-if="user.is_premium" class="font-semibold text-green-600">Premium ✅</span>
                    <span v-else class="font-semibold text-gray-800">Gratuit (limité à {{ user.trip_limit }} voyages)</span>
                </p>
                <p v-if="user.is_premium && user.premium_ends_at" class="mt-1 text-sm text-gray-600">
                    <template v-if="user.on_grace_period">
                        Annulé, actif jusqu’au <strong>{{ new Date(user.premium_ends_at).toLocaleDateString() }}</strong>
                    </template>
                    <template v-else>
                        Valide jusqu’au <strong>{{ new Date(user.premium_ends_at).toLocaleDateString() }}</strong>
                    </template>
                </p>

            </div>

            <!-- Actions -->
            <div class="mt-4 flex flex-wrap gap-3">
                <!-- Checkout -->
                <form v-if="!user.is_premium" method="POST" :action="route('billing.checkout')" class="inline">
                    <input type="hidden" name="_token" :value="csrfToken()" />
                    <PrimaryButton type="submit">Passer Premium</PrimaryButton>
                </form>

                <!-- Portal -->
                <form v-if="user.is_premium" method="POST" :action="route('billing.portal')" class="inline">
                    <input type="hidden" name="_token" :value="csrfToken()" />
                    <SecondaryButton type="submit">Gérer mon abonnement</SecondaryButton>
                </form>

                <!-- Cancel -->
                <form
                    v-if="user.is_premium"
                    method="POST"
                    :action="route('billing.cancel')"
                    class="inline"
                    onsubmit="return confirm('Annuler votre abonnement ? Il restera actif jusqu’à la fin de la période en cours.');"
                >
                    <input type="hidden" name="_token" :value="csrfToken()" />
                    <SecondaryButton type="submit">Annuler (fin de période)</SecondaryButton>
                </form>
            </div>

            <!-- Factures -->
            <div class="mt-6">
                <h4 class="text-sm font-medium text-gray-900">Mes factures</h4>

                <div v-if="user.invoices?.length" class="mt-2 space-y-2">
                    <div v-for="inv in user.invoices" :key="inv.id" class="flex items-center justify-between rounded border p-3">
                        <div>
                            <div class="font-medium">#{{ inv.number }}</div>
                            <div class="text-sm text-gray-600">
                                {{ new Date(inv.date).toLocaleDateString() }} — {{ inv.total }}
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <a v-if="inv.hosted_invoice_url" :href="inv.hosted_invoice_url" target="_blank" class="underline">Voir</a>
                            <a v-if="inv.pdf" :href="inv.pdf" target="_blank" class="underline">PDF</a>
                        </div>
                    </div>
                </div>

                <p v-else class="mt-2 text-sm text-gray-600">Aucune facture disponible.</p>
            </div>
        </template>
    </ActionSection>
</template>
