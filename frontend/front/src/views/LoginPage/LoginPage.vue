<template>
  <div class="login">
    <form class="login__form" @submit.prevent="onSubmit">
      <div class="login__heading">
        <span class="login__badge">С возвращением</span>
        <h2 class="login__title">Войдите в аккаунт</h2>
        <p>Продолжите работу над своими досками.</p>
      </div>

      <p v-if="authStore.error" class="login__request-error" role="alert">
        {{ authStore.error }}
      </p>

      <div class="login__field">
        <label class="login__label">Email</label>

        <input 
          v-model="loginForm.email" 
          @input="validateEmail" 
          class="login__input" 
          type="email" 
          placeholder="you@example.com" 
        />

        <span
          v-if="errorsMessage.email"
          class="login__field-error"
        >
          {{ errorsMessage.email }}
        </span>
      </div>

      <div class="login__field">
        <label class="login__label">Пароль</label>

        <input
          v-model="loginForm.password"
          @input="validatePassword" 
          class="login__input"
          type="password"
          placeholder="Введите пароль"
        />

        <span
          v-if="errorsMessage.password"
          class="login__field-error"
        >
          {{ errorsMessage.password }}
        </span>
      </div>

      <button :class="activeBtn" :disabled="!isFormFilled || authStore.loading" type="submit">Войти</button>

      <div v-show="authStore.loading" class="login__loader" role="status" aria-live="polite">
        <span class="login__spinner" aria-hidden="true"></span>
        <span>Вход в аккаунт...</span>
      </div>

      <p class="login__switch">
        Впервые здесь?
        <RouterLink to="/register">Создать аккаунт</RouterLink>
      </p>
    </form>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { validateForm } from '@/utils/validateForm';
import { useAuthStore } from '@/stores/AuthStore'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

interface LoginForm {
  email: string
  password: string
}


const loginForm = ref<LoginForm >({
  email: '',
  password: ''
})

const errorsMessage = ref<LoginForm>({
  email: '',
  password: ''
}) 

const activeBtn = computed((): string => {
  return validateForm(loginForm.value)
    ? 'login__button'
    : 'login__button login__button--disabled'
})

const isFormFilled = computed((): boolean => {
  return validateForm(loginForm.value)
})

const validateEmail = (): void => {
  if (!loginForm.value.email.trim()) {
    errorsMessage.value.email = 'Email обязателен'
    return
  }

  if (
    loginForm.value.email.length < 5 ||
    !loginForm.value.email.includes('@')
  ) {
    errorsMessage.value.email = 'Email некорректный'
    return
  }

  errorsMessage.value.email = ''
}

const validatePassword = (): void => {
  if (loginForm.value.password.trim().length < 5) {
    errorsMessage.value.password = 'Пароль не может быть меньше 5 символов'
    return
  }

  errorsMessage.value.password = ''
}


async function onSubmit() {
  if (!validateForm(loginForm.value)) return false

  const login = await authStore.login(loginForm.value) 

  if (login) {
    router.push({ name: 'workspace' })
  }
}



</script>

<style src="./LoginPage.scss" />

<style scoped>
.login__request-error {
  padding: 11px 12px;
  color: #fecdd3;
  font-size: 13px;
  line-height: 1.4;
  background: rgba(244, 63, 94, .12);
  border: 1px solid rgba(251, 113, 133, .26);
  border-radius: 10px;
}

.login__loader {
  display: inline-flex;
  gap: 9px;
  align-items: center;
  justify-content: center;
  color: #94a3b8;
  font-size: 13px;
}

.login__spinner {
  width: 17px;
  height: 17px;
  border: 2px solid rgba(56, 189, 248, .22);
  border-top-color: #38bdf8;
  border-radius: 50%;
  animation: spin .7s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
