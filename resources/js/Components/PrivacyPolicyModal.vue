<script setup>
import { ref, onMounted } from 'vue'

const showModal = ref(false)

onMounted(() => {
    const consent = localStorage.getItem('privacyConsent')
    if (!consent) showModal.value = true
})

function acceptAll() {
    localStorage.setItem('privacyConsent', 'accepted')
    showModal.value = false
}

function rejectAll() {
    localStorage.setItem('privacyConsent', 'rejected')
    showModal.value = false
}
</script>

<template>
    <transition name="fade">
        <div
            v-if="showModal"
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
        >
            <div class="bg-white rounded-2xl shadow-lg max-w-lg w-full p-6 text-gray-800">
                <h2 class="text-xl font-semibold mb-3">
                    Politique de confidentialité
                </h2>

                <p class="mb-3 text-sm text-gray-600">
                    Nous utilisons des cookies et collectons certaines données personnelles
                    (comme votre adresse e-mail et vos informations de navigation)
                    afin d’assurer le bon fonctionnement du site, d’améliorer nos services
                    et de personnaliser votre expérience utilisateur.
                </p>

                <p class="mb-3 text-sm text-gray-600">
                    Vos données sont traitées conformément au Règlement Général sur la
                    Protection des Données (RGPD) et à la législation belge en vigueur.
                    Vous pouvez retirer votre consentement à tout moment.
                    Pour en savoir plus, consultez notre
                    <a href="/privacy-policy" class="text-blue-600 hover:underline">
                        politique de confidentialité complète
                    </a>.
                </p>

                <div class="flex justify-end gap-3 mt-6">
                    <button
                        @click="rejectAll"
                        class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100"
                    >
                        Refuser
                    </button>
                    <button
                        @click="acceptAll"
                        class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700"
                    >
                        Accepter
                    </button>
                </div>
            </div>
        </div>
    </transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
