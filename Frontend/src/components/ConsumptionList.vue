<script setup lang="ts">
import { Consumption } from '../types/devcoffee'

defineProps<{
  consumptions: Consumption[]
}>()

const emit = defineEmits<{
  (e: 'delete', deleteHref: string): void
}>()
</script>

<template>
  <section class="card">
    <h2>Historique des consommations</h2>
    <div v-if="consumptions.length === 0">Aucune consommation enregistrée.</div>
    <table v-else>
      <thead>
        <tr>
          <th>Développeur</th>
          <th>Boisson</th>
          <th>Caféine</th>
          <th>Date</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="c in consumptions" :key="c.id">
          <td>{{ c.developerName }}</td>
          <td>{{ c.drinkName }}</td>
          <td>{{ c.caffeineMg }} mg</td>
          <td>{{ c.consumedAt }}</td>
          <td>
            <button
              v-if="c._links?.delete"
              class="danger"
              @click="emit('delete', c._links.delete.href)"
            >
              Supprimer
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </section>
</template>