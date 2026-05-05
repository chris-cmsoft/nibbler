<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppHeader from '@/components/AppHeader.vue';
import AppShell from '@/components/AppShell.vue';
import TeamPetActivityToasts from '@/components/TeamPetActivityToasts.vue';
import { Toaster } from '@/components/ui/sonner';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { BreadcrumbItem } from '@/types';
import type { Team } from '@/types/teams';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const currentTeam = computed(() => page.props.currentTeam as Team | null);
</script>

<template>
    <AppShell variant="header">
        <AppHeader :breadcrumbs="breadcrumbs" />
        <AppContent variant="header">
            <slot />
        </AppContent>
        <TeamPetActivityToasts
            v-if="currentTeam"
            :key="currentTeam.id"
            :team-id="currentTeam.id"
        />
        <Toaster />
    </AppShell>
</template>
