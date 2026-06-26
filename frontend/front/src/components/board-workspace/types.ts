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
  id: string
  title: string
  description: string
  items: BoardDefectCard[]
}
