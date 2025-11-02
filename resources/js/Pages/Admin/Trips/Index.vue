<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({
    trips: Object,
})

function deleteTrip(trip) {
    if (confirm(`Supprimer le voyage "${trip.title}" ?`)) {
        router.delete(route('admin.trips.destroy', trip.id))
    }
}
</script>

<template>
    <Head title="Voyages - Admin" />

    <div class="p-6 max-w-6xl mx-auto">
        <h1 class="text-2xl font-semibold mb-6">🌍 Liste des voyages</h1>

        <div class="overflow-x-auto bg-white shadow rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">ID</th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Titre</th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Auteur</th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Budget (€)</th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Public</th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Créé le</th>
                    <th class="px-4 py-2 text-right text-sm font-semibold text-gray-700">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                <tr
                    v-for="trip in trips.data"
                    :key="trip.id"
                    class="hover:bg-gray-50"
                >
                    <td class="px-4 py-2 text-sm text-gray-700">{{ trip.id }}</td>
                    <td class="px-4 py-2 text-sm font-medium text-gray-900">
                        {{ trip.title }}
                    </td>
                    <td class="px-4 py-2 text-sm text-gray-700">
                        {{ trip.user?.name ?? '—' }}
                    </td>
                    <td class="px-4 py-2 text-sm text-gray-700">
                        {{ trip.budget ?? '—' }}
                    </td>
                    <td class="px-4 py-2 text-sm">
                            <span
                                :class="trip.is_public
                                    ? 'bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-semibold'
                                    : 'bg-gray-100 text-gray-600 px-2 py-1 rounded-full text-xs font-semibold'"
                            >
                                {{ trip.is_public ? 'Oui' : 'Non' }}
                            </span>
                    </td>
                    <td class="px-4 py-2 text-sm text-gray-500">
                        {{ new Date(trip.created_at).toLocaleDateString() }}
                    </td>
                    <td class="px-4 py-2 text-sm text-right space-x-2">
                        <Link
                            :href="route('public.trips.show', trip.id)"
                            class="text-pink-600 hover:underline"
                            target="_blank"
                        >
                            Voir
                        </Link>
                        <button
                            @click="deleteTrip(trip)"
                            class="text-red-600 hover:underline"
                        >
                            Supprimer
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6 flex justify-center flex-wrap gap-2">
            <template v-for="link in trips.links" :key="link.label">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    v-html="link.label"
                    class="px-3 py-1 rounded text-sm transition"
                    :class="[
                        link.active
                            ? 'bg-pink-600 text-white font-semibold'
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                    ]"
                />
                <span
                    v-else
                    v-html="link.label"
                    class="px-3 py-1 rounded text-sm bg-gray-50 text-gray-400 cursor-not-allowed"
                />
            </template>
        </div>
    </div>
</template>
