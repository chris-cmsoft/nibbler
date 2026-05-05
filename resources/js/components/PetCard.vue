<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import { HandHeart, Utensils } from 'lucide-vue-next';
import { reactive, watch } from 'vue';
import {
    feed,
    pet as petPet,
} from '@/actions/App/Http/Controllers/PetController';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import type { Pet } from '@/types';

type PetCareUpdated = {
    petId: number;
    calorieLevel: number;
    attentionLevel: number;
};

const props = defineProps<{
    currentTeamSlug: string;
    pet: Pet;
}>();

const care = reactive({
    calorieLevel: props.pet.calorieLevel,
    attentionLevel: props.pet.attentionLevel,
});

watch(
    () => [props.pet.calorieLevel, props.pet.attentionLevel],
    ([calorieLevel, attentionLevel]) => {
        care.calorieLevel = calorieLevel;
        care.attentionLevel = attentionLevel;
    },
);

useEcho<PetCareUpdated>(
    `pets.${props.pet.id}`,
    'PetCareUpdated',
    (event) => {
        care.calorieLevel = event.calorieLevel;
        care.attentionLevel = event.attentionLevel;
    },
);

function progressWidth(value: number): string {
    return `${Math.min(100, Math.max(0, value))}%`;
}
</script>

<template>
    <Card class="overflow-hidden rounded-lg" data-test="pet-card">
        <CardHeader class="gap-2">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <CardTitle>{{ pet.name }}</CardTitle>
                    <CardDescription>
                        {{ pet.animal.name }} born {{ pet.birthday }}
                    </CardDescription>
                </div>
            </div>
        </CardHeader>

        <CardContent class="flex flex-col gap-5">
            <div
                class="flex aspect-video items-center justify-center rounded-lg border bg-muted/40 p-4"
            >
                <img
                    :src="pet.animal.svgUrl"
                    :alt="`${pet.name} the ${pet.animal.name}`"
                    class="h-full max-h-48 w-full object-contain"
                />
            </div>

            <div class="space-y-4">
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-medium">Calories</span>
                        <span
                            class="text-muted-foreground"
                            data-test="pet-calories"
                        >
                            {{ care.calorieLevel }} / 100
                        </span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-muted">
                        <div
                            class="h-full rounded-full bg-emerald-500"
                            :style="{ width: progressWidth(care.calorieLevel) }"
                        />
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-medium">Stimulation</span>
                        <span
                            class="text-muted-foreground"
                            data-test="pet-stimulation"
                        >
                            {{ care.attentionLevel }} / 100
                        </span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-muted">
                        <div
                            class="h-full rounded-full bg-sky-500"
                            :style="{
                                width: progressWidth(care.attentionLevel),
                            }"
                        />
                    </div>
                </div>
            </div>

            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div class="rounded-lg border p-3">
                    <dt class="text-muted-foreground">Daily calories</dt>
                    <dd class="font-medium">
                        {{ pet.animal.caloriesPerDay }}
                    </dd>
                </div>
                <div class="rounded-lg border p-3">
                    <dt class="text-muted-foreground">Attention need</dt>
                    <dd class="font-medium">
                        {{ pet.animal.attentionPoints }}
                    </dd>
                </div>
            </dl>
        </CardContent>

        <CardFooter class="gap-2">
            <Form
                :action="
                    feed({
                        current_team: currentTeamSlug,
                        pet: pet.id,
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
                        current_team: currentTeamSlug,
                        pet: pet.id,
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
</template>
