<template>
  <section class="status-column">
    <header class="status-column__head">
      <div>
        <h3>{{ column.title }}</h3>
        <span>{{ column.description }}</span>
      </div>
      <strong>{{ column.items.length }}</strong>
    </header>

    <div class="status-column__items">
      <DefectCard
        v-for="item in column.items"
        :key="item.id"
        :item="item"
      />

      <p v-if="column.items.length === 0" class="status-column__empty">
        В этой колонке пока нет дефектов.
      </p>
    </div>
  </section>
</template>

<script setup lang="ts">
import DefectCard from '../defect-card/DefectCard.vue'
import type { BoardStatusColumn } from '../types'

defineProps<{
  column: BoardStatusColumn
}>()
</script>

<style src="./status-column.scss" lang="scss" />
