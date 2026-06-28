<template>
  <article class="defect-card">
    <div class="defect-card__top">
      <span class="defect-card__code">DEF-{{ item.id }}</span>
      <span class="defect-card__executor">{{ item.executer_name ?? `Исполнитель #${item.executer_id}` }}</span>
    </div>

    <h4>{{ item.title }}</h4>
    <p>{{ item.description }}</p>

    <footer class="defect-card__footer">
      <span>Позиция {{ item.position }}</span>
      <span>Автор: {{ item.appointed_name ?? `#${item.appointed_id}` }}</span>
    </footer>
  </article>
</template>

<script setup lang="ts">
import type { BoardDefectCard } from '@/Ts/status'

defineProps<{
  item: BoardDefectCard
}>()
</script>

<style src="./defect-card.scss" lang="scss" />
