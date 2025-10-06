<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<template>
    <div class="flex flex-col items-center justify-center min-h-[70vh] text-center p-6">
        <div class="max-w-md bg-surface-container rounded-2xl shadow-md p-8">
            <h2 class="text-2xl font-semibold mb-4 text-on-surface">
                Créez votre premier voyage ✈️
            </h2>
            <p class="text-on-surface-variant mb-6">
                Connectez-vous ou créez un compte pour planifier vos aventures, ajouter vos étapes et garder vos roadbooks personnels.
            </p>
            <Link
                href="/register"
                class="inline-block bg-primary text-white font-medium py-3 px-6 rounded-full hover:bg-primary/90 transition"
            >
                S’inscrire
            </Link>
        </div>
    </div>
</template>
