<script setup>
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps({
    trips: Object
})
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
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                <tr v-for="trip in trips.data" :key="trip.id" class="hover:bg-gray-50">
                    <td class="px-4 py-2 text-sm text-gray-700">{{ trip.id }}</td>
                    <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ trip.title }}</td>
                    <td class="px-4 py-2 text-sm text-gray-700">
                        {{ trip.user?.name ?? '–' }}
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
                </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6 flex justify-center space-x-2">
            <Link
                v-for="link in trips.links"
                :key="link.label"
                :href="link.url"
                v-html="link.label"
                class="px-3 py-1 rounded text-sm"
                :class="[
          link.active ? 'bg-pink-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200',
          !link.url && 'opacity-50 cursor-not-allowed'
        ]"
            />
        </div>
    </div>
</template>
