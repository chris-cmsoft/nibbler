<script setup lang="ts">
import { PawPrint } from 'lucide-vue-next';
</script>

<template>
    <div class="flex min-w-0 items-center gap-2">
        <div
            class="flex aspect-square size-8 items-center justify-center rounded-md bg-emerald-600 text-white"
        >
            <PawPrint class="size-5" />
        </div>
        <div class="grid min-w-0 flex-1 text-left text-sm">
            <span class="mb-0.5 truncate leading-tight font-semibold"
                >Nibbler</span
            >
        </div>
    </div>
</template>
