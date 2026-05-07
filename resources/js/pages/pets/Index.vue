<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import { PawPrint } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { index as adoptionsIndex } from '@/actions/App/Http/Controllers/AdoptionController';
import { index } from '@/actions/App/Http/Controllers/PetController';
import Heading from '@/components/Heading.vue';
import PetCard from '@/components/PetCard.vue';
import { Button } from '@/components/ui/button';
import type { Pet, Team } from '@/types';

type Props = {
    pets: Pet[];
};

type PetAdopted = {
    teamId: number;
    pet: Pet;
};

type PetReturned = {
    teamId: number;
    petId: number;
};

const props = defineProps<Props>();

const page = usePage();

const currentTeam = computed(() => page.props.currentTeam as Team | null);
const currentTeamSlug = computed(() => currentTeam.value?.slug ?? '');
const teamChannel = currentTeam.value
    ? `teams.${currentTeam.value.id}`
    : 'teams.0';
const teamPets = ref<Pet[]>([...props.pets]);

watch(
    () => props.pets,
    (pets) => {
        teamPets.value = [...pets];
    },
);

useEcho<PetAdopted>(teamChannel, 'PetAdopted', (event) => {
    const existingPetIndex = teamPets.value.findIndex(
        (pet) => pet.id === event.pet.id,
    );

    if (existingPetIndex === -1) {
        teamPets.value = [...teamPets.value, event.pet].sort((a, b) =>
            a.name.localeCompare(b.name),
        );

        return;
    }

    teamPets.value[existingPetIndex] = event.pet;
});

useEcho<PetReturned>(teamChannel, 'PetReturned', (event) => {
    teamPets.value = teamPets.value.filter((pet) => pet.id !== event.petId);
});

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
            v-if="teamPets.length > 0"
            class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3"
        >
            <PetCard
                v-for="teamPet in teamPets"
                :key="teamPet.id"
                :current-team-slug="currentTeamSlug"
                :pet="teamPet"
            />
        </div>

        <div
            v-else
            class="flex flex-col items-center gap-4 rounded-lg border border-dashed p-10 text-center"
        >
            <div class="flex max-w-md flex-col items-center gap-2">
                <div
                    class="flex size-12 items-center justify-center rounded-full border bg-muted/40"
                >
                    <PawPrint class="size-5 text-muted-foreground" />
                </div>
                <h2 class="text-lg font-semibold">Adopt your first pet</h2>
                <p class="text-sm text-muted-foreground">
                    Choose an available animal, give it a name, and it will
                    appear here for the whole team.
                </p>
            </div>

            <Button as-child>
                <Link
                    :href="adoptionsIndex({ current_team: currentTeamSlug })"
                    prefetch
                >
                    <PawPrint />
                    Browse adoptions
                </Link>
            </Button>
        </div>
    </div>
</template>
