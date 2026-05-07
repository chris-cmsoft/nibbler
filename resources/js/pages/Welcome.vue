<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowRight, Heart, LogIn, PawPrint, Sparkles } from 'lucide-vue-next';
import { computed } from 'vue';
import { index as petsIndex } from '@/actions/App/Http/Controllers/PetController';
import { login, register } from '@/routes';
import type { Animal, Team } from '@/types';

type Props = {
    animals: Animal[];
    canRegister: boolean;
};

withDefaults(defineProps<Props>(), {
    canRegister: true,
});

const page = usePage();

const currentTeam = computed(() => page.props.currentTeam as Team | null);
const petsUrl = computed(() =>
    currentTeam.value ? petsIndex(currentTeam.value.slug).url : '/',
);
</script>

<template>
    <Head title="Adopt on Nibbler" />

    <main
        class="min-h-screen bg-stone-50 text-stone-950 dark:bg-neutral-950 dark:text-stone-50"
    >
        <header class="border-b border-stone-200/80 dark:border-stone-800">
            <nav
                class="mx-auto flex h-16 w-full max-w-6xl items-center justify-between px-4 sm:px-6 lg:px-8"
            >
                <div class="flex items-center gap-2 font-semibold">
                    <span
                        class="flex size-9 items-center justify-center rounded-md bg-emerald-600 text-white"
                    >
                        <PawPrint class="size-5" />
                    </span>
                    Nibbler
                </div>

                <div class="flex items-center gap-2">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="petsUrl"
                        class="inline-flex items-center gap-2 rounded-md border border-stone-300 px-3 py-2 text-sm font-medium hover:bg-stone-100 dark:border-stone-700 dark:hover:bg-stone-900"
                    >
                        <PawPrint class="size-4" />
                        Pets
                    </Link>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium hover:bg-stone-100 dark:hover:bg-stone-900"
                        >
                            <LogIn class="size-4" />
                            Log in
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="register()"
                            class="inline-flex items-center gap-2 rounded-md bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700"
                        >
                            <Heart class="size-4" />
                            Adopt
                        </Link>
                    </template>
                </div>
            </nav>
        </header>

        <section
            class="mx-auto grid w-full max-w-6xl gap-10 px-4 py-10 sm:px-6 lg:grid-cols-[0.9fr_1.1fr] lg:px-8 lg:py-14"
        >
            <div class="flex flex-col justify-center gap-6">
                <div
                    class="inline-flex w-fit items-center gap-2 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-900 dark:border-emerald-900/70 dark:bg-emerald-950 dark:text-emerald-100"
                >
                    <Sparkles class="size-4" />
                    Available adoptions
                </div>

                <div class="space-y-4">
                    <h1
                        class="max-w-2xl text-4xl font-semibold tracking-normal text-balance sm:text-5xl"
                    >
                        Find your next tiny teammate on Nibbler.
                    </h1>
                    <p
                        class="max-w-xl text-lg leading-8 text-stone-600 dark:text-stone-300"
                    >
                        Choose a pet, make an account, and start caring for them
                        with your team.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <Link
                        v-if="canRegister"
                        :href="register()"
                        class="inline-flex items-center gap-2 rounded-md bg-emerald-600 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-700"
                    >
                        Adopt a pet
                        <ArrowRight class="size-4" />
                    </Link>
                    <Link
                        :href="login()"
                        class="inline-flex items-center gap-2 rounded-md border border-stone-300 px-5 py-3 text-sm font-semibold hover:bg-stone-100 dark:border-stone-700 dark:hover:bg-stone-900"
                    >
                        Log in
                    </Link>
                </div>
            </div>

            <div
                v-if="animals.length > 0"
                class="grid grid-cols-1 gap-4 sm:grid-cols-2"
            >
                <article
                    v-for="animal in animals"
                    :key="animal.id"
                    class="flex min-h-72 flex-col justify-between rounded-lg border border-stone-200 bg-white p-5 shadow-sm dark:border-stone-800 dark:bg-stone-900"
                    data-test="public-adoption-card"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-semibold">
                                {{ animal.name }}
                            </h2>
                            <p
                                class="mt-1 text-sm text-stone-600 dark:text-stone-300"
                            >
                                Ready for a new team.
                            </p>
                        </div>
                    </div>

                    <div
                        class="flex aspect-video items-center justify-center p-4"
                    >
                        <img
                            :src="animal.svgUrl"
                            :alt="animal.name"
                            class="h-full max-h-40 w-full object-contain"
                        />
                    </div>

                    <dl class="grid grid-cols-2 gap-3 text-sm">
                        <div
                            class="rounded-md bg-stone-100 p-3 dark:bg-stone-800"
                        >
                            <dt class="text-stone-600 dark:text-stone-300">
                                Calories
                            </dt>
                            <dd class="font-semibold">
                                {{ animal.caloriesPerDay }}
                            </dd>
                        </div>
                        <div
                            class="rounded-md bg-stone-100 p-3 dark:bg-stone-800"
                        >
                            <dt class="text-stone-600 dark:text-stone-300">
                                Attention
                            </dt>
                            <dd class="font-semibold">
                                {{ animal.attentionPoints }}
                            </dd>
                        </div>
                    </dl>
                </article>
            </div>

            <div
                v-else
                class="flex min-h-80 items-center justify-center rounded-lg border border-dashed border-stone-300 bg-white p-8 text-center text-stone-600 dark:border-stone-700 dark:bg-stone-900 dark:text-stone-300"
            >
                New adoptable pets will be available soon.
            </div>
        </section>
    </main>
</template>
