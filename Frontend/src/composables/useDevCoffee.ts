import { ref } from 'vue'
import { Drink, Consumption, ProblemDetails, EcoMetrics } from '../types/devcoffee'

const API_BASE = 'http://localhost:8000/api'

export function useDevCoffee() {
  const drinks = ref<Drink[]>([])
  const consumptions = ref<Consumption[]>([])
  const error = ref<ProblemDetails | null>(null)
  const ecoMetrics = ref<EcoMetrics>({
    responseTimeMs: null,
    payloadSizeBytes: null,
    estimatedCo2Grams: null
  })

  const extractEcoMetrics = (response: Response) => {
    ecoMetrics.value = {
      responseTimeMs: response.headers.get('X-Response-Time-Ms'),
      payloadSizeBytes: response.headers.get('X-Payload-Size-Bytes'),
      estimatedCo2Grams: response.headers.get('X-Estimated-CO2-Grams')
    }
  }

  const fetchDrinks = async () => {
    try {
      const res = await fetch(`${API_BASE}/drinks`)
      extractEcoMetrics(res)
      if (res.ok) {
        drinks.value = await res.json()
      }
    } catch (err) {
      console.error('Erreur chargement boissons', err)
    }
  }

  const fetchConsumptions = async () => {
    try {
      const res = await fetch(`${API_BASE}/consumptions`)
      extractEcoMetrics(res)
      if (res.ok) {
        consumptions.value = await res.json()
      }
    } catch (err) {
      console.error('Erreur chargement consommations', err)
    }
  }

  const createConsumption = async (payload: { drinkId: number; developerName: string }) => {
    error.value = null
    try {
      const res = await fetch(`${API_BASE}/consumptions`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      })
      extractEcoMetrics(res)

      if (!res.ok) {
        const problem: ProblemDetails = await res.json()
        error.value = problem
        return false
      }

      await fetchConsumptions()
      return true
    } catch (err) {
      console.error('Erreur réseau création consommation', err)
      return false
    }
  }

  const deleteConsumption = async (deleteUrl: string) => {
    error.value = null
    try {
      const res = await fetch(deleteUrl, {
        method: 'DELETE'
      })
      extractEcoMetrics(res)

      if (!res.ok && res.status !== 204) {
        try {
          const problem: ProblemDetails = await res.json()
          error.value = problem
        } catch {
          // Ignorer si pas de JSON d'erreur
        }
        return false
      }

      await fetchConsumptions()
      return true
    } catch (err) {
      console.error('Erreur réseau suppression', err)
      return false
    }
  }

  return {
    drinks,
    consumptions,
    error,
    ecoMetrics,
    fetchDrinks,
    fetchConsumptions,
    createConsumption,
    deleteConsumption
  }
}