import { ref } from "vue";
import { defineStore } from "pinia";
import { api } from "@/utils/api";
import { execute, type RequestState } from "@/utils/execute";
import type { NewInvite } from "@/Ts/invite";
import type { ApiMessage } from "@/Ts/api";

export const useInviteStore = defineStore('inviteStore', () => {
    const loading = ref(false)
    const error = ref<string | null>(null)
    const message = ref<string | null>(null)

    const states: RequestState = {loading, error, message}

    const sendInvite = async (boardId: number | string, newInvite: NewInvite): Promise<boolean> => {
        const data = await execute<ApiMessage>(() => api.post(`/board/${boardId}/invite`, newInvite, {
            withCredentials: true,
        }), states)

        if (data !== null) {
            message.value = data.message
        }

        return data !== null
    }

    return {
        loading,
        error,
        message,
        sendInvite,
    }
})
