import axios from 'axios'

const envApiBaseUrl = import.meta.env.VITE_API_BASE_URL?.trim()
const apiBaseUrl = envApiBaseUrl || (import.meta.env.DEV ? 'http://localhost:8000/api' : '/api')

export const api = axios.create({
  baseURL: apiBaseUrl,
  timeout: 10000,
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json',
  },
})
