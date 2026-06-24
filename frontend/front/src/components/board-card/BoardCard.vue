<template>
  <article class="board-card">
    <div class="board-card__top">
      <span class="board-card__mark">{{ title.slice(0, 1).toUpperCase() }}</span>
      <span class="board-card__status">Активна</span>
    </div>

    <div class="board-card__content">
      <h3>{{ title }}</h3>
      <p>{{ description }}</p>
    </div>

    <footer class="board-card__footer">
      <span class="board-card__members">{{ membersCount }} участника</span>
      <span class="board-card__role">{{ getRoleLabel(role) }}</span>
    </footer>
  </article>
</template>

<script setup lang="ts">
import { getRoleLabel } from '@/Ts/role'

defineProps<{
  title: string
  description: string
  membersCount: number
  role: string
}>()
</script>

<style src="./board-card.scss" lang="scss" />
