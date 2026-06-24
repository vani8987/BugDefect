import LoginPage from '@/views/LoginPage/LoginPage.vue'
import RegisterPage from '@/views/RegisterPage/RegisterPage.vue'
import WorkspacePage from '@/views/WorkspacePage/WorkspacePage.vue'
import BoardPage from '@/views/BoardPage/BoardPage.vue'
import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/AuthStore'


const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: { name: 'workspace' },
    },
    {
      path: '/register',
      name: 'reg',
      component: RegisterPage,
    },
    {
      path: '/login',
      name: 'login',
      component: LoginPage,
    },
    {
      path: '/workspace',
      name: 'workspace',
      component: WorkspacePage,
      meta: { requiredAuth: true },
    },
    {
      path: '/boards/:boardId',
      name: 'board',
      component: BoardPage,
      meta: { requiredAuth: true },
    }
  ],
})

router.beforeEach(async (to, _from, next) => {
  if (!to.meta.requiredAuth) {
    return next()
  }

  const authStore = useAuthStore()
  await authStore.me()

  if (authStore.user === null) {
    authStore.resetFeedback()
    return next({ name: 'login' })
  }

  next()
})


export default router
