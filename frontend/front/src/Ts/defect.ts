export interface NewDefect {
  title: string
  description?: string
  statusId: number
  executorID: number
}

export interface MoveDefect {
  statusId: number
  position: number
}

export interface DeleteDefect {
  statusId: number
}
