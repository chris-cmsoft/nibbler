<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { PawPrint, Store } from 'lucide-vue-next';
import { computed } from 'vue';
import { index as adoptionsIndex } from '@/actions/App/Http/Controllers/AdoptionController';
import { index as petsIndex } from '@/actions/App/Http/Controllers/PetController';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import TeamSwitcher from '@/components/TeamSwitcher.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import type { NavItem } from '@/types';

const page = usePage();

const petsUrl = computed(() =>
    page.props.currentTeam ? petsIndex(page.props.currentTeam.slug).url : '/',
);

const adoptionsUrl = computed(() =>
    page.props.currentTeam
        ? adoptionsIndex(page.props.currentTeam.slug).url
        : '/',
);

const mainNavItems = computed<NavItem[]>(() => [
    {
        title: 'Pets',
        href: petsUrl.value,
        icon: PawPrint,
    },
    {
        title: 'Adoptions',
        href: adoptionsUrl.value,
        icon: Store,
    },
]);
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="petsUrl">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
            <SidebarMenu>
                <SidebarMenuItem>
                    <TeamSwitcher />
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
