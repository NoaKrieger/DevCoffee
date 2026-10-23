<script setup lang="ts">
import { EcoMetrics } from '../types/devcoffee'

defineProps<{
  metrics: EcoMetrics
}>()
</script>

<template>
  <div class="eco-badge">
    <span><strong>Temps serveur :</strong> {{ metrics.responseTimeMs ? metrics.responseTimeMs + ' ms' : '-' }}</span>
    <span><strong>Taille payload :</strong> {{ metrics.payloadSizeBytes ? metrics.payloadSizeBytes + ' octets' : '-' }}</span>
    <span><strong>CO2 estimé :</strong> {{ metrics.estimatedCo2Grams ? Number(metrics.estimatedCo2Grams).toFixed(6) + ' g' : '-' }}</span>
  </div>
</template>