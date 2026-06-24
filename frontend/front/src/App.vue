<template>
  <div class="app-shell">
    <Header />

    <main class="app-shell__content">
      <routerView />
    </main>
  </div>
</template>

<script setup lang="ts">
import Header from './components/header/Header.vue'
</script>
