import { defineStore } from 'pinia'
import { ref } from 'vue'
import type {
  CurrentUserResponse,
  LoginUser,
  RegisterUser,
  UserAuth,
} from '@/Ts/user'
import type { ApiMessage } from '@/Ts/api'
import { api } from '@/utils/api'
import {
  execute,
  resetFeedback as resetRequestFeedback,
  type RequestState,
} from '@/utils/execute'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<UserAuth | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const message = ref<string | null>(null)
  const requestState: RequestState = { loading, error, message }

  function resetFeedback(): void {
    resetRequestFeedback(requestState)
  }

  async function register(newUser: RegisterUser): Promise<boolean> {
    const data = await execute<ApiMessage>(() => api.post('/register', newUser), requestState)

    if (data !== null) {
      message.value = data.message
    }

    return data !== null
  }

  async function login(loginUser: LoginUser): Promise<boolean> {
    const data = await execute<ApiMessage>(() => api.post('/login', loginUser), requestState)

    if (data !== null) {
      message.value = data.message
    }

    return data !== null
  }

  async function logout(): Promise<boolean> {
    const data = await execute<ApiMessage>(() => api.post('/logout'), requestState)

    if (data !== null) {
      message.value = data.message
      user.value = null
    }

    return data !== null
  }

  async function me(): Promise<UserAuth | null> {
    const data = await execute<CurrentUserResponse>(() => api.get('/me'), requestState)

    if (data !== null) {
      user.value = data.user
    }

    return user.value
  }

  return {
    user,
    loading,
    error,
    message,
    resetFeedback,
    register,
    login,
    logout,
    me,
  }
})
