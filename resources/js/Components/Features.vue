<script setup>
const features = [
    {
        icon: "map",
        title: "Créez vos itinéraires",
        description: "Planifiez chaque étape de votre voyage avec notre outil intuitif",
        gradient: "from-blue-500 to-cyan-400",
    },
    {
        icon: "group",
        title: "Partagez vos aventures",
        description: "Inspirez d'autres voyageurs en partageant vos meilleures découvertes",
        gradient: "from-teal-500 to-emerald-400",
    },
    {
        icon: "bookmark",
        title: "Sauvegardez facilement",
        description: "Gardez tous vos voyages organisés en un seul endroit",
        gradient: "from-orange-400 to-amber-500",
    },
    {
        icon: "auto_awesome",
        title: "Découvrez l'inspiration",
        description: "Explorez des milliers d'itinéraires créés par la communauté",
        gradient: "from-purple-500 to-pink-500",
    },
]
</script>

<template>
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-6">
            <!-- Titre principal -->
            <div class="text-center max-w-3xl mx-auto mb-12">
                <h2 class="text-3xl md:text-4xl font-bold mb-4">
                    Tout ce dont vous avez besoin pour
                    <span
                        class="bg-gradient-to-r from-blue-600 to-teal-500 bg-clip-text text-transparent"
                    >
            voyager sereinement
          </span>
                </h2>
            </div>

            <!-- Grille -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div
                    v-for="feature in features"
                    :key="feature.title"
                    class="bg-white  rounded-3xl shadow-sm hover:shadow-md border border-gray-100  p-6 transition-all group"
                >
                    <div
                        class="w-12 h-12 rounded-2xl flex items-center justify-center mb-4 bg-gradient-to-br text-white group-hover:scale-110 transition-transform"
                        :class="feature.gradient"
                    >
                        <span class="material-symbol text-[26px]">{{ feature.icon }}</span>
                    </div>

                    <h3 class="font-semibold text-lg text-gray-900 ">
                        {{ feature.title }}
                    </h3>

                    <p class="text-gray-500  text-sm leading-relaxed mt-2">
                        {{ feature.description }}
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>
