<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import { computed, reactive } from 'vue';
import {
    index,
    store,
} from '@/actions/App/Http/Controllers/AdoptionController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Animal, Team } from '@/types';

type Props = {
    animals: Animal[];
};

defineProps<Props>();

const page = usePage();

const currentTeam = computed(() => page.props.currentTeam as Team | null);
const currentTeamSlug = computed(() => currentTeam.value?.slug ?? '');
const adoptionNames = reactive<Record<number, string>>({});
const pendingAdoptionNames = reactive<Record<number, string>>({});
const successfulAdoptionMessages = reactive<Record<number, string>>({});

function markAdoptionSuccessful(animalId: number): void {
    successfulAdoptionMessages[animalId] =
        `Successfully adopted ${pendingAdoptionNames[animalId]}`;
    adoptionNames[animalId] = '';
    delete pendingAdoptionNames[animalId];
}

function markAdoptionStarted(animalId: number): void {
    pendingAdoptionNames[animalId] = adoptionNames[animalId] ?? '';
}

function updateAdoptionName(animalId: number, name: string | number): void {
    adoptionNames[animalId] = String(name);
    delete successfulAdoptionMessages[animalId];
}

defineOptions({
    layout: (props: { currentTeam?: Team | null }) => ({
        breadcrumbs: [
            {
                title: 'Adoptions',
                href: props.currentTeam ? index(props.currentTeam.slug) : '/',
            },
        ],
    }),
});
</script>

<template>
    <Head title="Adoptions" />

    <div class="flex flex-col gap-6 p-4">
        <Heading
            variant="small"
            title="Adoptions"
            description="Choose a new pet for your team."
        />

        <div
            v-if="animals.length > 0"
            class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3"
        >
            <Card
                v-for="animal in animals"
                :key="animal.id"
                class="overflow-hidden rounded-lg"
                data-test="adoption-card"
            >
                <CardHeader>
                    <CardTitle>{{ animal.name }}</CardTitle>
                    <CardDescription>
                        Available for every team to adopt.
                    </CardDescription>
                </CardHeader>

                <CardContent class="flex flex-col gap-5">
                    <div
                        class="flex aspect-video items-center justify-center rounded-lg border bg-muted/40 p-4"
                    >
                        <img
                            :src="animal.svgUrl"
                            :alt="animal.name"
                            class="h-full max-h-48 w-full object-contain"
                        />
                    </div>

                    <dl class="grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-lg border p-3">
                            <dt class="text-muted-foreground">
                                Daily calories
                            </dt>
                            <dd class="font-medium">
                                {{ animal.caloriesPerDay }}
                            </dd>
                        </div>
                        <div class="rounded-lg border p-3">
                            <dt class="text-muted-foreground">
                                Attention need
                            </dt>
                            <dd class="font-medium">
                                {{ animal.attentionPoints }}
                            </dd>
                        </div>
                    </dl>
                </CardContent>

                <CardFooter>
                    <Form
                        :action="
                            store({
                                current_team: currentTeamSlug,
                                animal: animal.id,
                            })
                        "
                        #default="{ errors, processing }"
                        class="flex w-full flex-col gap-3"
                        @start="markAdoptionStarted(animal.id)"
                        @success="markAdoptionSuccessful(animal.id)"
                    >
                        <div class="space-y-2">
                            <Label :for="`animal-${animal.id}-name`">
                                Pet name
                            </Label>
                            <Input
                                :id="`animal-${animal.id}-name`"
                                :model-value="adoptionNames[animal.id] ?? ''"
                                name="name"
                                maxlength="50"
                                placeholder="Name your pet"
                                :aria-invalid="!!errors.name"
                                data-test="adoption-pet-name"
                                @update:model-value="
                                    updateAdoptionName(animal.id, $event)
                                "
                            />
                            <InputError :message="errors.name" />
                            <p
                                v-if="successfulAdoptionMessages[animal.id]"
                                class="text-sm text-emerald-600 dark:text-emerald-400"
                                data-test="adoption-success-message"
                            >
                                {{ successfulAdoptionMessages[animal.id] }}
                            </p>
                        </div>

                        <Button
                            type="submit"
                            :disabled="processing"
                            data-test="adopt-animal-button"
                        >
                            <Plus />
                            Adopt
                        </Button>
                    </Form>
                </CardFooter>
            </Card>
        </div>

        <div
            v-else
            class="rounded-lg border border-dashed p-10 text-center text-muted-foreground"
        >
            There are no animals available for adoption yet.
        </div>
    </div>
</template>
