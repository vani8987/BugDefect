import { defineStore } from "pinia"
import { ref } from "vue"
import type { BoardStatusColumn, NewStatus, StatusesResponse, UpdateStatusesPosition } from "@/Ts/status"
import type { ApiMessage } from "@/Ts/api"
import { api } from "@/utils/api"
import { execute, type RequestState } from "@/utils/execute"

export const useStatusesStore = defineStore('statusesStore', () => {
    const boardColumns = ref<BoardStatusColumn[]>([])

    const loading = ref(false)
    const error = ref<string | null>(null)
    const message = ref<string | null>(null)
    
    const states: RequestState = {loading, error, message}

    const getAll = async (boardId: number | string): Promise<BoardStatusColumn[]> => {
        const data = await execute<StatusesResponse>(() => api.get(`/status/${boardId}`), states)

        if (data !== null) {
            boardColumns.value = data.statuses.map((status) => ({
                ...status,
                items: [],
            }))
        }

        return boardColumns.value
    }

    const createStatus = async (boardId: number | string, newStatus: NewStatus): Promise<boolean> => {
        const data = await execute<ApiMessage>(() => api.post(`/status/${boardId}`, newStatus), states)

        if (data !== null) {
            message.value = data.message
            await getAll(boardId)
        }

        return data !== null
    }

    const updatePosition = async (
        boardId: number | string,
        statuses: UpdateStatusesPosition,
    ): Promise<boolean> => {
        const data = await execute<ApiMessage>(() => api.patch(`/status/${boardId}/position`, statuses), states)

        if (data !== null) {
            message.value = data.message
        }

        return data !== null
    }

    return {
        boardColumns,
        loading,
        error,
        message,
        getAll,
        createStatus,
        updatePosition
    }
})
