import axios, { type AxiosResponse } from 'axios'
import type { Ref } from 'vue'

export interface RequestState {
  loading: Ref<boolean>
  error: Ref<string | null>
  message: Ref<string | null>
}

function getErrorMessage(error: unknown): string {
  if (axios.isAxiosError<{ message?: string }>(error)) {
    return error.response?.data?.message ?? 'Не удалось выполнить запрос. Попробуйте ещё раз.'
  }

  return error instanceof Error ? error.message : 'Произошла неизвестная ошибка.'
}

export function resetFeedback(state: RequestState): void {
  state.error.value = null
  state.message.value = null
}

export async function execute<T>(
  request: () => Promise<AxiosResponse<T>>,
  state: RequestState,
): Promise<T | null> {
  state.loading.value = true
  resetFeedback(state)

  try {
    return (await request()).data
  } catch (error: unknown) {
    state.error.value = getErrorMessage(error)
    return null
  } finally {
    state.loading.value = false
  }
}
