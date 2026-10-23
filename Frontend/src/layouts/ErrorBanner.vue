<script setup lang="ts">
import { ProblemDetails } from '../types/devcoffee'

defineProps<{
  error: ProblemDetails | null
}>()
</script>

<template>
  <div v-if="error" class="error-banner">
    <strong>{{ error.title }} (Statut {{ error.status }})</strong>
    <p>{{ error.detail }}</p>
  </div>
</template>