export interface Drink {
  id: number;
  name: string;
  caffeineMg: number;
  volumeMl: number;
  _links?: {
    self: { href: string; method: string };
  };
}

export interface Consumption {
  id: number;
  developerName: string;
  drinkName: string;
  caffeineMg: number;
  consumedAt: string;
  _links?: {
    delete: { href: string; method: string };
  };
}

export interface ProblemDetails {
  type: string;
  title: string;
  status: number;
  detail: string;
}

export interface EcoMetrics {
  responseTimeMs: string | null;
  payloadSizeBytes: string | null;
  estimatedCo2Grams: string | null;
}