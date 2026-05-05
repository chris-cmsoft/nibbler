<script setup lang="ts">
import { useEcho } from '@laravel/echo-vue';
import { toast } from 'vue-sonner';

type PetAdopted = {
    actorName: string;
    petName: string;
};

type PetReturned = {
    actorName: string;
    petName: string;
};

const props = defineProps<{
    teamId: number;
}>();

const teamChannel = `teams.${props.teamId}`;

useEcho<PetAdopted>(teamChannel, 'PetAdopted', (event) => {
    toast(`${event.actorName} adopted ${event.petName}`);
});

useEcho<PetReturned>(teamChannel, 'PetReturned', (event) => {
    toast(`${event.actorName} returned ${event.petName}`);
});
</script>

<template>
    <span class="hidden" aria-hidden="true" />
</template>
