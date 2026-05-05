<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { HandHeart, Utensils } from 'lucide-vue-next';
import { computed } from 'vue';
import {
    feed,
    index,
    pet as petPet,
} from '@/actions/App/Http/Controllers/PetController';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import type { Pet, Team } from '@/types';

type Props = {
    pets: Pet[];
};

defineProps<Props>();

const page = usePage();

const currentTeam = computed(() => page.props.currentTeam as Team | null);

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

function progressWidth(value: number): string {
    return `${Math.min(100, Math.max(0, value))}%`;
}
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
            <Card
                v-for="teamPet in pets"
                :key="teamPet.id"
                class="overflow-hidden rounded-lg"
                data-test="pet-card"
            >
                <CardHeader class="gap-2">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <CardTitle>{{ teamPet.name }}</CardTitle>
                            <CardDescription>
                                {{ teamPet.animal.name }} born
                                {{ teamPet.birthday }}
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>

                <CardContent class="flex flex-col gap-5">
                    <div
                        class="flex aspect-video items-center justify-center rounded-lg border bg-muted/40 p-4"
                    >
                        <img
                            :src="teamPet.animal.svgPath"
                            :alt="`${teamPet.name} the ${teamPet.animal.name}`"
                            class="h-full max-h-48 w-full object-contain"
                        />
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-2">
                            <div
                                class="flex items-center justify-between text-sm"
                            >
                                <span class="font-medium">Calories</span>
                                <span
                                    class="text-muted-foreground"
                                    data-test="pet-calories"
                                >
                                    {{ teamPet.calorieLevel }} / 100
                                </span>
                            </div>
                            <div
                                class="h-2 overflow-hidden rounded-full bg-muted"
                            >
                                <div
                                    class="h-full rounded-full bg-emerald-500"
                                    :style="{
                                        width: progressWidth(
                                            teamPet.calorieLevel,
                                        ),
                                    }"
                                />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div
                                class="flex items-center justify-between text-sm"
                            >
                                <span class="font-medium">Stimulation</span>
                                <span
                                    class="text-muted-foreground"
                                    data-test="pet-stimulation"
                                >
                                    {{ teamPet.attentionLevel }} / 100
                                </span>
                            </div>
                            <div
                                class="h-2 overflow-hidden rounded-full bg-muted"
                            >
                                <div
                                    class="h-full rounded-full bg-sky-500"
                                    :style="{
                                        width: progressWidth(
                                            teamPet.attentionLevel,
                                        ),
                                    }"
                                />
                            </div>
                        </div>
                    </div>

                    <dl class="grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-lg border p-3">
                            <dt class="text-muted-foreground">
                                Daily calories
                            </dt>
                            <dd class="font-medium">
                                {{ teamPet.animal.caloriesPerDay }}
                            </dd>
                        </div>
                        <div class="rounded-lg border p-3">
                            <dt class="text-muted-foreground">
                                Attention need
                            </dt>
                            <dd class="font-medium">
                                {{ teamPet.animal.attentionPoints }}
                            </dd>
                        </div>
                    </dl>
                </CardContent>

                <CardFooter v-if="currentTeam" class="gap-2">
                    <Form
                        :action="
                            feed({
                                current_team: currentTeam.slug,
                                pet: teamPet.id,
                            })
                        "
                        #default="{ processing }"
                    >
                        <Button
                            type="submit"
                            :disabled="processing"
                            data-test="pet-feed-button"
                        >
                            <Utensils />
                            Feed
                        </Button>
                    </Form>

                    <Form
                        :action="
                            petPet({
                                current_team: currentTeam.slug,
                                pet: teamPet.id,
                            })
                        "
                        #default="{ processing }"
                    >
                        <Button
                            type="submit"
                            :disabled="processing"
                            variant="secondary"
                            data-test="pet-pet-button"
                        >
                            <HandHeart />
                            Pet
                        </Button>
                    </Form>
                </CardFooter>
            </Card>
        </div>

        <div
            v-else
            class="rounded-lg border border-dashed p-10 text-center text-muted-foreground"
        >
            This team does not have any pets yet.
        </div>
    </div>
</template>
