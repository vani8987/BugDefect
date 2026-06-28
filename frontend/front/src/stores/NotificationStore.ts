import type { notificationInvite, responseNotification } from "@/Ts/notification";
import type { ApiMessage } from "@/Ts/api";
import { api } from "@/utils/api";
import { execute, type RequestState } from "@/utils/execute";
import { defineStore } from "pinia";
import { ref } from "vue";
import { useBoardStore } from "./boardStore";



export const useNotificationStore = defineStore('notificationStore', () => {
    const boardStore = useBoardStore()

    const notifications = ref<notificationInvite[]>([])
    const loading = ref(false)
    const error = ref<string | null>(null)
    const message = ref<string | null>(null)

    const states: RequestState = {loading, error, message}

    const getAll = async (): Promise<notificationInvite[] | false> => {
        const res = await execute<responseNotification>(
            () => api.get('/notifications', {withCredentials: true}),
            states
        )

        if (res !== null)  {
            notifications.value = res.notifications.map((notification) => ({
                ...notification,
                is_read: Boolean(Number(notification.is_read)),
            }))
            return notifications.value
        }

        return false
    }

    const updateAll = async () => {
        const res = await execute<ApiMessage>(
            () => api.patch('/notifications/read', {is_read: true}, {withCredentials: true}),
            states
        )

        if (res !== null)  {
           message.value = res.message
           notifications.value = notifications.value.map((notification) => ({
            ...notification,
            is_read: true,
           }))
           return true
        }

        return false
    }

    const inviteClick = async (boardId: number, inviteId: number, mode: 'accept' | 'reject') => {
        const res = await execute<ApiMessage>(
            () => api.post(`/boards/${boardId}/invite/${mode}`, {invite_id: inviteId}, {withCredentials: true}),
            states
        )

        if (res !== null)  {
           message.value = res.message
           notifications.value = notifications.value.map((notification) => {
            if (notification.data?.invite_id !== inviteId) return notification

            return {
                ...notification,
                is_read: true,
                data: {
                    ...notification.data,
                    status: mode === 'accept' ? 'accepted' : 'declined',
                },
            }
           })

           if (mode === 'accept') {
            await boardStore.getAll()
           }

           return true
        }

        return false
    }

    const clear = (): void => {
        notifications.value = []
    }

    return {
        notifications,
        loading,
        error,
        message,
        getAll,
        updateAll,
        inviteClick,
        clear
    }
})
