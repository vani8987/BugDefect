export type DefectPriority = 'low' | 'medium' | 'high'

export interface BoardDefectCard {
    id: number
    code: string
    title: string
    description: string
    priority: DefectPriority
    assignee: string
    due: string
}

export interface BoardStatusColumn {
    id: number
    title: string
    description: string
    position: number
    items: BoardDefectCard[]
}

export interface StatusResponse {
    id: number
    board_id: number
    title: string
    description: string
    position: number
}

export interface StatusesResponse {
    statuses: StatusResponse[]
}

export interface NewStatus {
    title: string
    description?: string
    position: number
}

export interface StatusPosition {
    id: number
    position: number
}

export interface UpdateStatusesPosition {
    statuses: StatusPosition[]
}
