<script setup lang="ts">
import { onMounted } from 'vue'
import { useDevCoffee } from '../composables/useDevCoffee'
import ErrorBanner from '../layouts/ErrorBanner.vue'
import ConsumptionForm from '../components/ConsumptionForm.vue'
import ConsumptionList from '../components/ConsumptionList.vue'
import EcoBadge from '../components/EcoBadge.vue'

const {
  drinks,
  consumptions,
  error,
  ecoMetrics,
  fetchDrinks,
  fetchConsumptions,
  createConsumption,
  deleteConsumption
} = useDevCoffee()

onMounted(async () => {
  await fetchDrinks()
  await fetchConsumptions()
})

const handleCreate = async (payload: { drinkId: number; developerName: string }) => {
  await createConsumption(payload)
}

const handleDelete = async (deleteHref: string) => {
  await deleteConsumption(deleteHref)
}
</script>

<template>
  <main>
    <img src="../assets/logo.svg" alt="Logo DevCoffee" class="logo" />
    <h1>DevCoffee Tracker</h1>

    <ErrorBanner :error="error" />

    <ConsumptionForm :drinks="drinks" @submit="handleCreate" />

    <ConsumptionList :consumptions="consumptions" @delete="handleDelete" />

    <EcoBadge :metrics="ecoMetrics" />
  </main>
</template>