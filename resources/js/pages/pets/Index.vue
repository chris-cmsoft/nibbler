<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { index } from '@/actions/App/Http/Controllers/PetController';
import Heading from '@/components/Heading.vue';
import PetCard from '@/components/PetCard.vue';
import type { Pet, Team } from '@/types';

type Props = {
    pets: Pet[];
};

defineProps<Props>();

const page = usePage();

const currentTeam = computed(() => page.props.currentTeam as Team | null);
const currentTeamSlug = computed(() => currentTeam.value?.slug ?? '');

defineOptions({
    layout: (props: { currentTeam?: Team | null }) => ({
        breadcrumbs: [
            {
                title: 'Pets',
                href: props.currentTeam ? index(props.currentTeam.slug) : '/',
            },
        ],
    }),
});
</script>

<template>
    <Head title="Pets" />

    <div class="flex flex-col gap-6 p-4">
        <Heading
            variant="small"
            title="Pets"
            description="Care for your team's pets."
        />

        <div
            v-if="pets.length > 0"
            class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3"
        >
            <PetCard
                v-for="teamPet in pets"
                :key="teamPet.id"
                :current-team-slug="currentTeamSlug"
                :pet="teamPet"
            />
        </div>

        <div
            v-else
            class="rounded-lg border border-dashed p-10 text-center text-muted-foreground"
        >
            This team does not have any pets yet.
        </div>
    </div>
</template>
