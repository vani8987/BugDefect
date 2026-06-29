import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { ApiMessage } from '@/Ts/api'
import type { MoveDefect, NewDefect } from '@/Ts/defect'
import { api } from '@/utils/api'
import { execute, type RequestState } from '@/utils/execute'

export const useDefectsStore = defineStore('defectsStore', () => {
  const loading = ref(false)
  const error = ref<string | null>(null)
  const message = ref<string | null>(null)

  const states: RequestState = { loading, error, message }

  const createDefect = async (
    boardId: number | string,
    defect: NewDefect,
  ): Promise<boolean> => {
    const data = await execute<ApiMessage>(
      () => api.post(`/boards/${boardId}/defects`, defect),
      states,
    )

    if (data !== null) {
      message.value = data.message
    }

    return data !== null
  }

  const moveDefect = async (
    boardId: number | string,
    defectId: number,
    defect: MoveDefect,
  ): Promise<boolean> => {
    const data = await execute<ApiMessage>(
      () => api.patch(`/boards/${boardId}/defects/${defectId}/move`, defect),
      states,
    )

    if (data !== null) {
      message.value = data.message
    }

    return data !== null
  }

  return {
    loading,
    error,
    message,
    createDefect,
    moveDefect,
  }
})
