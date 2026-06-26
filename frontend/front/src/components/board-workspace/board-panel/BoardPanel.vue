<template>
  <section class="board-panel">
    <div class="board-panel__heading">
      <div>
        <p class="board-panel__eyebrow">Задачи</p>
        <h2>Рабочее пространство</h2>
      </div>

      <div class="board-panel__actions">
        <UiButton
          v-if="canManage"
          variant="secondary"
          @click="$emit('addStatus')"
        >
          Добавить статус
        </UiButton>
        <UiButton
          v-if="canManage"
          @click="$emit('addDefect')"
        >
          Новый дефект
        </UiButton>
      </div>
    </div>

    <div class="board-panel__kanban" aria-label="Доска дефектов">
      <StatusColumn
        v-for="column in columns"
        :key="column.id"
        :column="column"
      />
    </div>
  </section>
</template>

<script setup lang="ts">
import UiButton from '@/components/ui/button/UiButton.vue'
import StatusColumn from '../status-column/StatusColumn.vue'
import type { BoardStatusColumn } from '../types'

defineProps<{
  columns: BoardStatusColumn[]
  canManage: boolean
}>()

defineEmits<{
  addDefect: []
  addStatus: []
}>()
</script>

<style src="./board-panel.scss" lang="scss" />
