<template>
  <header
    class="header"
    :class="{
      'header--menu-open': isMobileMenuOpen,
      'header--notifications-open': isNotificationsOpen,
    }"
  >
    <RouterLink class="header__brand" to="/workspace">
      <span class="header__mark">B</span>
      <span>BugDefect</span>
    </RouterLink>

    <button
      class="header__mobile-toggle"
      type="button"
      aria-label="Открыть меню"
      :aria-expanded="isMobileMenuOpen"
      @click="isMobileMenuOpen = !isMobileMenuOpen"
    >
      <TbMenu2 />
    </button>

    <nav class="header__nav" aria-label="Навигация">
      <div v-if="authStore.user" class="header__page-switcher">
        <RouterLink class="header__page-link" to="/workspace">Рабочая зона</RouterLink>
      </div>

      <div v-if="!authStore.user" class="header__guest-actions">
        <RouterLink class="header__link" to="/login">Войти</RouterLink>
        <RouterLink class="header__link header__link--accent" to="/register">Регистрация</RouterLink>
      </div>

      <div v-else class="header__user-actions">
        <div class="header__profile">
          <span class="header__avatar">
            {{ authStore.user.name.slice(0, 1).toUpperCase() }}
          </span>
          <div class="header__profile-info">
            <div class="header__meta">
              <span class="header__status">В сети</span>
              <span class="header__role">{{ getRoleLabel(authStore.user.role) }}</span>
            </div>
            <span class="header__name">{{ authStore.user.name }}</span>
          </div>
        </div>

        <div class="header__menu-tools">
          <div class="header__notifications">
            <button
              class="header__notification"
              type="button"
              aria-label="Уведомления"
              title="Уведомления"
              :aria-expanded="isNotificationsOpen"
              @click="isNotificationsOpen = !isNotificationsOpen"
            >
              <span class="header__notification__count">1</span>
              <IoNotificationsOutline />
            </button>

            <Transition name="notification-menu">
              <section v-if="isNotificationsOpen" class="header__notification-menu" aria-label="Уведомления">
                <div class="header__notification-heading">
                  <div>
                    <span>Уведомления</span>
                    <small>1 новое</small>
                  </div>
                  <span class="header__notification-dot" aria-hidden="true"></span>
                </div>

                <article class="header__notification-item">
                  <span class="header__notification-icon"><IoNotificationsOutline /></span>
                  <div>
                    <strong>Добро пожаловать в BugDefect</strong>
                    <p>Здесь появятся приглашения в доски и важные обновления.</p>
                  </div>
                </article>
              </section>
            </Transition>
          </div>

          <button class="header__settings" type="button" aria-label="Настройки" title="Настройки (будет сделан позже)">
            <IpSetting />
          </button>

          <button class="header__logout" type="button" aria-label="Выйти из аккаунта" @click="logout">
            <IpLogout />
          </button>
        </div>
      </div>
    </nav>
  </header>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useAuthStore } from '@/stores/AuthStore';
import { IoNotificationsOutline } from 'vue-icons-plus/io';
import { IpHamburger, IpLogout, IpSetting } from 'vue-icons-plus/ip';
import { TbMenu2 } from 'vue-icons-plus/tb';
import { useRouter } from 'vue-router';
import { getRoleLabel } from '@/Ts/role'

const authStore = useAuthStore()
const router = useRouter()
const isMobileMenuOpen = ref<boolean>(false)
const isNotificationsOpen = ref<boolean>(false)

const logout = async () => {
  await authStore.logout()
  router.push('/login')
}

</script>

<style src="./header.scss" lang="scss" />
