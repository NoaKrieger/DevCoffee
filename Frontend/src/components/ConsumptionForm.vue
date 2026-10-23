<script setup lang="ts">
import { ref } from 'vue'
import { Drink } from '../types/devcoffee'

const props = defineProps<{
  drinks: Drink[]
}>()

const emit = defineEmits<{
  (e: 'submit', payload: { drinkId: number; developerName: string }): void
}>()

const developerName = ref('')
const selectedDrinkId = ref<number | ''>('')

const onSubmit = () => {
  if (!developerName.value || !selectedDrinkId.value) return
  emit('submit', {
    drinkId: Number(selectedDrinkId.value),
    developerName: developerName.value.trim()
  })
  selectedDrinkId.value = ''
}
</script>

<template>
  <section class="card">
    <h2>Enregistrer une consommation</h2>
    <form @submit.prevent="onSubmit">
      <div>
        <label for="developerName">Nom du développeur</label>
        <input
          id="developerName"
          v-model="developerName"
          type="text"
          placeholder="Ex: Valérian"
          required
        />
      </div>

      <div>
        <label for="drinkSelect">Boisson</label>
        <select id="drinkSelect" v-model="selectedDrinkId" required>
          <option value="" disabled>-- Sélectionner une boisson --</option>
          <option v-for="drink in props.drinks" :key="drink.id" :value="drink.id">
            {{ drink.name }} ({{ drink.caffeineMg }}mg - {{ drink.volumeMl }}ml)
          </option>
        </select>
      </div>

      <button type="submit">Enregistrer</button>
    </form>
  </section>
</template>