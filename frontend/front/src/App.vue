<template>
  <div class="app-shell">
    <Header />

    <main class="app-shell__content">
      <routerView />
    </main>

    <Transition name="app-loader">
      <div v-if="authStore.loading" class="app-shell__loader" role="status" aria-live="polite">
        <span class="app-shell__spinner" aria-hidden="true"></span>
        <span>Загрузка...</span>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import Header from './components/header/Header.vue'
import { useAuthStore } from '@/stores/AuthStore'

const authStore = useAuthStore()
</script>

<style scoped lang="scss">
.app-shell__loader {
  position: fixed;
  inset: 0;
  z-index: 1000;
  display: flex;
  gap: 12px;
  align-items: center;
  justify-content: center;
  color: #e2e8f0;
  font-size: 14px;
  font-weight: 600;
  background: rgba(15, 23, 42, .72);
  backdrop-filter: blur(8px);
}

.app-shell__spinner {
  width: 22px;
  height: 22px;
  border: 3px solid rgba(148, 163, 184, .28);
  border-top-color: #38bdf8;
  border-radius: 50%;
  animation: app-loader-spin .75s linear infinite;
}

.app-loader-enter-active,
.app-loader-leave-active {
  transition: opacity .18s ease;
}

.app-loader-enter-from,
.app-loader-leave-to {
  opacity: 0;
}

@keyframes app-loader-spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
