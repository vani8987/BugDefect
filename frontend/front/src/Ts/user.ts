export interface UserAuth {
  id: number
  name: string
  email: string
  role_id: number
  role: string
}

export interface LoginUser {
  email: string
  password: string
}

export interface RegisterUser extends LoginUser {
  name: string
}

export interface ApiMessage {
  message: string
}

export interface CurrentUserResponse {
  user: UserAuth
}

export interface ApiErrorResponse {
  message?: string
}
