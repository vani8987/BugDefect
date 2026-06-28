export interface BoardDefectCard {
    id: number
    title: string
    description: string
    executer_id: number
    board_id: number
    status_id: number
    appointed_id: number
    position: number
    executer_name?: string
    appointed_name?: string
}

export type DefectResponse = BoardDefectCard

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
    items?: DefectResponse[]
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
