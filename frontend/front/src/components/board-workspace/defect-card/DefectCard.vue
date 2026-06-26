<template>
  <article class="defect-card">
    <div class="defect-card__top">
      <span class="defect-card__code">{{ item.code }}</span>
      <span class="defect-card__priority" :class="`defect-card__priority--${item.priority}`">
        {{ priorityLabel }}
      </span>
    </div>

    <h4>{{ item.title }}</h4>
    <p>{{ item.description }}</p>

    <footer class="defect-card__footer">
      <span>{{ item.assignee }}</span>
      <time>{{ item.due }}</time>
    </footer>
  </article>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { BoardDefectCard, DefectPriority } from '../types'

const props = defineProps<{
  item: BoardDefectCard
}>()

const priorityLabels: Record<DefectPriority, string> = {
  low: 'Низкий',
  medium: 'Средний',
  high: 'Высокий',
}

const priorityLabel = computed(() => priorityLabels[props.item.priority])
</script>

<style src="./defect-card.scss" lang="scss" />
