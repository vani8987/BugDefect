import { defineStore } from "pinia";
import { ref } from "vue";
import { execute, type RequestState } from "@/utils/execute";
import { api } from "@/utils/api";
import type { newBoard, messageBoarde, board, boardsResponse, oneBoardResponse, boardStatistic, boardMember, boardMembersResponse } from "@/Ts/board";

export const useBoardStore = defineStore('boardStore', () => {
    const boards = ref<board[]>([])
    const statistic = ref<boardStatistic | null>(null)
    const currentBoard = ref<board | null>(null)
    const members = ref<boardMember[]>([])
    const loading = ref(false)
    const error = ref<string | null>(null)
    const message = ref<string | null>(null)

    const states: RequestState = {loading, error, message}

    const crateBoard = async (newBoard: newBoard) => {
        const data = await execute<messageBoarde>(() => api.post('createBoard', newBoard , {
            withCredentials: true,
        }), states)

        if (data !== null) {
            message.value = data.message
        }

        return data !== null
    }

    const getAll = async (): Promise<board[]> => {
        const data = await execute<boardsResponse>(() => api.get('boards', {
            withCredentials: true,
        }), states)

        if (data !== null) {
            boards.value = data.boards
            statistic.value = data.stat
        }

        return boards.value
    }

    const getOneBoard = async (id: number | string): Promise<board | null> => {
        currentBoard.value = null

        const data = await execute<oneBoardResponse>(() => api.get(`boards/${id}`, {
            withCredentials: true,
        }), states)

        if (data !== null) {
            currentBoard.value = data.board
        }

        return currentBoard.value
    }

    const getBoardMembers = async (id: number | string): Promise<boardMember[]> => {
        members.value = []

        const data = await execute<boardMembersResponse>(() => api.get(`boards/${id}/members`, {
            withCredentials: true,
        }), states)

        if (data !== null) {
            members.value = data.members
        }

        return members.value
    }

    const deleteBoard = async (id: number | string): Promise<boolean> => {
        const data = await execute<messageBoarde>(() => api.delete(`boards/${id}`, {
            withCredentials: true,
        }), states)

        if (data !== null) {
            message.value = data.message
        }

        return data !== null
    }

    const deleteUserInBoarde = async (boardId: number | string, userId: number | string): Promise<boolean> => {
        const data = await execute<messageBoarde>(() => api.delete(`boards/${boardId}/members/${userId}`, {
            withCredentials: true,
        }), states)

        if (data !== null) {
            message.value = data.message
        }

        return data !== null
    }

    return {
        boards,
        currentBoard,
        members,
        loading,
        error,
        message,
        crateBoard,
        deleteBoard,
        getAll,
        getOneBoard,
        getBoardMembers,
        deleteUserInBoarde,
        statistic
    }
})
