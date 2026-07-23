export type { BoardDefectCard, BoardStatusColumn } from '@/Ts/status'

export interface DeleteDefectPayload {
  boardId: number
  statusId: number
  defectId: number
}
