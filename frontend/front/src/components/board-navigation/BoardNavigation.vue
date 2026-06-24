<template>
  <nav class="board-navigation" aria-label="Разделы доски">
    <RouterLink
      class="board-navigation__link"
      :to="{ name: 'board', params: { boardId } }"
    >
      <IoGridOutline />
      Доска
    </RouterLink>
    <RouterLink
      class="board-navigation__link"
      :to="{ name: 'board-overview', params: { boardId } }"
    >
      <IoStatsChartOutline />
      Обзор
    </RouterLink>
  </nav>
</template>

<script setup lang="ts">
import { IoGridOutline, IoStatsChartOutline } from 'vue-icons-plus/io'

defineProps<{
  boardId: string
}>()
</script>

<style src="./board-navigation.scss" lang="scss" />
