<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { Toaster as SonnerPrimitive } from 'vue-sonner';
import 'vue-sonner/style.css';

const { appearance } = useAppearance();
</script>

<template>
    <SonnerPrimitive
        :theme="appearance"
        class="toaster group"
        position="bottom-right"
        :style="{
            '--normal-bg':
                'color-mix(in oklch, var(--popover) 88%, oklch(0.685 0.169 237.323) 12%)',
            '--normal-text': 'var(--popover-foreground)',
            '--normal-border':
                'color-mix(in oklch, var(--border) 70%, oklch(0.685 0.169 237.323) 30%)',
        }"
    />
</template>
