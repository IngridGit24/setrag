export type User = {
  id: number
  email: string
  full_name: string
  role: string
}

const API_BASE = import.meta.env.VITE_API_URL ?? 'http://localhost:8000/api'

export function getUsersBaseUrl(): string {
  return import.meta.env.VITE_API_URL ?? 'http://localhost:8000/api'
}

export function getToken(): string | null {
  return localStorage.getItem('auth_token')
}

export function setToken(token: string): void {
  localStorage.setItem('auth_token', token)
}

export function clearToken(): void {
  localStorage.removeItem('auth_token')
}

export async function login(email: string, password: string): Promise<string> {
  const response = await fetch(`${API_BASE}/oauth/token`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: new URLSearchParams({
      username: email,
      password: password,
    }),
  })

  if (!response.ok) {
    const errorData = await response.json().catch(() => ({}))
    throw new Error(errorData.error || 'Identifiants invalides')
  }

  const data = await response.json()
  return data.access_token
}

export async function fetchMe(token?: string): Promise<any> {
  const authToken = token || getToken()
  if (!authToken) {
    throw new Error('Token non trouvé')
  }

  const response = await fetch(`${API_BASE}/me`, {
    headers: {
      'Authorization': `Bearer ${authToken}`,
    },
  })

  if (!response.ok) {
    throw new Error('Erreur lors de la récupération des données utilisateur')
  }

  return response.json()
}


