export interface board {
    id: number
    title: string
    member_count: number
    description: string
    owner_id: number
    role: string
}

export interface boardStatistic {
    boards_count: number
}

export interface boardsResponse {
    boards: board[]
    stat: boardStatistic
}

export interface oneBoardResponse {
    board: board
}

export interface boardMember {
    id: number
    name: string
    email: string
    role: string
}

export interface boardMembersResponse {
    members: boardMember[]
}

export interface newBoard {
    title: string
    description?: string
}

export interface createBorder{
    message: string
}
