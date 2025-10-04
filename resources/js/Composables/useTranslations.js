import { usePage } from '@inertiajs/vue3'

/**
 * Composable simple pour récupérer les traductions envoyées
 * par Laravel via Inertia (HandleInertiaRequests).
 *
 * Usage :
 *   import { t } from '@/composables/useTranslations'
 *   t('dashboard.title')
 */
export function useTranslations() {
    const page = usePage()

    function t(key, replacements = {}) {
        const translations = page.props.translations || {}
        const keys = key.split('.')

        let value = translations
        for (const k of keys) {
            if (value[k] === undefined) {
                return key // fallback : renvoie la clé brute
            }
            value = value[k]
        }

        // Remplacement type Laravel : ":name" => "John"
        if (typeof value === 'string') {
            Object.entries(replacements).forEach(([search, replace]) => {
                value = value.replace(`:${search}`, replace)
            })
        }

        return value
    }

    return { t }
}

// raccourci : import { t } direct
export const { t } = useTranslations()
