export interface notificationInvite {
    id: number
    title: string
    type: 'invite' | 'info'
    description: string
    user_id: number
    data: dataInvite | null
    is_read: boolean
}

interface dataInvite {
    'invite_id': number
    'board_id': number
    status?: 'pending' | 'accepted' | 'declined'
}

export interface responseNotification {
    'notifications': notificationInvite[]
}
