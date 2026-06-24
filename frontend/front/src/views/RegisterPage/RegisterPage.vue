<template>
  <div class="register">
    <form class="register__form" @submit.prevent="onSubmit">
      <div class="register__heading">
        <span class="register__badge">НОВЫЙ АККАУНТ</span>
        <h1 class="register__title">Создайте пространство<br />для своей команды</h1>
        <p>Начните вести задачи и дефекты в одном рабочем месте.</p>
      </div>

      <p v-if="authStore.error" class="register__request-error" role="alert">
        {{ authStore.error }}
      </p>

      <div class="register__field">
        <label class="register__label">Имя</label>

        <input
          v-model="registerForm.name"
          @input="validateName"
          class="register__input"
          type="text"
          placeholder="Как к вам обращаться"
        />

        <span v-if="errorsMessage.name" class="register__field-error">
          {{ errorsMessage.name }}
        </span>
      </div>

      <div class="register__field">
        <label class="register__label">Email</label>

        <input
          v-model="registerForm.email"
          @input="validateEmail"
          class="register__input"
          type="email"
          placeholder="you@example.com"
        />

        <span v-if="errorsMessage.email" class="register__field-error">
          {{ errorsMessage.email }}
        </span>
      </div>

      <div class="register__field">
        <label class="register__label">Пароль</label>

        <input
          v-model="registerForm.password"
          @input="validatePassword"
          class="register__input"
          type="password"
          placeholder="Минимум 5 символов"
        />

        <span v-if="errorsMessage.password" class="register__field-error">
          {{ errorsMessage.password }}
        </span>
      </div>

      <div class="register__field">
        <label class="register__label">Повторите пароль</label>

        <input
          v-model="registerForm.confirmPassword"
          @input="validateConfirmPassword"
          class="register__input"
          type="password"
          placeholder="Повторите пароль"
        />

        <span v-if="errorsMessage.confirmPassword" class="register__field-error">
          {{ errorsMessage.confirmPassword }}
        </span>
      </div>

      <button :class="activeBtn" :disabled="!isFormFilled || authStore.loading" type="submit">
        Создать аккаунт
      </button>

      <div v-show="authStore.loading" class="register__loader" role="status" aria-live="polite">
        <span class="register__spinner" aria-hidden="true"></span>
        <span>Создаём аккаунт...</span>
      </div>

      <p class="register__switch">
        Уже есть аккаунт?
        <RouterLink to="/login">Войти</RouterLink>
      </p>
    </form>

  </div>
</template>

<script setup lang="ts">
import { validateForm } from '@/utils/validateForm'
import { computed, ref } from 'vue'
import { useAuthStore } from '@/stores/AuthStore'
import { useRouter } from 'vue-router'


const authStore = useAuthStore()
const router = useRouter()


interface RegisterForm {
  name: string
  email: string
  password: string
  confirmPassword: string
}

const registerForm = ref<RegisterForm>({
  name: '',
  email: '',
  password: '',
  confirmPassword: '',
})

const errorsMessage = ref<RegisterForm>({
  name: '',
  email: '',
  password: '',
  confirmPassword: '',
})

const activeBtn = computed((): string => {
  return validateForm(registerForm.value) ? 'register__button' : 'register__button register__button register__button--disabled'
})

const isFormFilled = computed((): boolean => {
  return validateForm(registerForm.value)
})

const validateName = (): void => {
  if (registerForm.value.name.trim().length < 3) {
    errorsMessage.value.name = 'Имя не может быть меньше 3 символов'
    return
  }

  errorsMessage.value.name = ''
}

const validateEmail = (): void => {
  if (!registerForm.value.email.trim()) {
    errorsMessage.value.email = 'Email обязателен'
    return
  }

  if (
    registerForm.value.email.length < 5 ||
    !registerForm.value.email.includes('@')
  ) {
    errorsMessage.value.email = 'Email некорректный'
    return
  }

  errorsMessage.value.email = ''
}

const validatePassword = (): void => {
  if (registerForm.value.password.trim().length < 5) {
    errorsMessage.value.password = 'Пароль не может быть меньше 5 символов'
    return
  }

  errorsMessage.value.password = ''

  if (registerForm.value.confirmPassword) {
    validateConfirmPassword()
  }
}

const validateConfirmPassword = (): void => {
  if (registerForm.value.confirmPassword !== registerForm.value.password) {
    errorsMessage.value.confirmPassword = 'Пароли не совпадают'
    return
  }

  errorsMessage.value.confirmPassword = ''
}

const allValidate = () => {
  validateName() 
  validateEmail()
  validatePassword()
  validateConfirmPassword()
}

async function onSubmit() {
  allValidate()

  if (
    errorsMessage.value.name ||
    errorsMessage.value.email ||
    errorsMessage.value.password ||
    errorsMessage.value.confirmPassword
  ) {
    return
  }

  const register = await authStore.register({
    name: registerForm.value.name,
    email: registerForm.value.email,
    password: registerForm.value.password,
  })

  if (register) {
    router.push({ name: 'login' })
  }
}
</script>

<style src="./RegisterPage.scss" />

<style scoped>
.register__request-error {
  padding: 11px 12px;
  color: #fecdd3;
  font-size: 13px;
  line-height: 1.4;
  background: rgba(244, 63, 94, .12);
  border: 1px solid rgba(251, 113, 133, .26);
  border-radius: 10px;
}

.register__loader {
  display: inline-flex;
  gap: 9px;
  align-items: center;
  justify-content: center;
  color: #94a3b8;
  font-size: 13px;
}

.register__spinner {
  width: 17px;
  height: 17px;
  border: 2px solid rgba(167, 139, 250, .2);
  border-top-color: #a78bfa;
  border-radius: 50%;
  animation: spin .7s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
