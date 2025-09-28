// resources/js/composables/useStepSummary.js
import { computed } from 'vue'

/**
 * Normalise les étapes d’un voyage
 */
export function useStepsSummary(steps) {
    return computed(() => {
        const raw = Array.isArray(steps) ? steps : []
        return [...raw]
            .sort((a, b) => (a.order ?? 0) - (b.order ?? 0))
            .map((s, idx) => {
                const day = s.order ?? (idx + 1)
                return {
                    id: s.id,
                    day,
                    short_title: `Jour ${day}`,
                    title: s.title || s.location || `Étape ${day}`,
                    location: s.location,
                    photo_url: s.photo_url ?? null,
                    description_html: s.description_html ?? null,
                    coords: {
                        lat: s.latitude ? Number(s.latitude) : null,
                        lng: s.longitude ? Number(s.longitude) : null,
                    },
                }
            })
    })
}

/**
 * Extrait les métadonnées principales d’un voyage
 */
export function useTripMeta(trip) {
    return computed(() => ({
        title: trip?.title || '',
        destination: trip?.destination || trip?.location || '',
        start_date: trip?.start_date,
        end_date: trip?.end_date,
        visibility: trip?.is_public ? 'public' : 'private',
        budget: trip?.budget,
        currency: trip?.currency || 'EUR',
    }))
}
