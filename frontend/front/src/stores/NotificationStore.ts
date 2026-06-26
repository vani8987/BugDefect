import type { notificationInvite, responseNotification, responseNotificationMessage } from "@/Ts/notification";
import { api } from "@/utils/api";
import { execute, type RequestState } from "@/utils/execute";
import { defineStore } from "pinia";
import { ref } from "vue";


export const useNotificationStore = defineStore('notificationStore', () => {
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
        const res = await execute<responseNotificationMessage>(
            () => api.patch('/notifications/read', {is_read: true}, {withCredentials: true}),
            states
        )

        if (res !== null)  {
           message.value = res.message
           return true
        }

        notifications.value = notifications.value.map((notification) => ({
            ...notification,
            is_read: true,
        }))

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
        clear
    }
})
