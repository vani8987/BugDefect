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

    <div
      v-if="columns.length > 0"
      class="board-panel__kanban"
      aria-label="Доска дефектов"
    >
      <StatusColumn
        v-for="column in columns"
        :key="column.id"
        :column="column"
        :can-manage="canManage"
        @status-drag-start="statusDragStart"
        @status-drop="statusDrop"
        @status-drag-end="statusDragEnd"
        @delete-status="deleteStatus"
        @defect-drag-start="defectDragStart"
        @defect-drop="defectDrop"
      />
    </div>

    <div v-else class="board-panel__empty">
      <div class="board-panel__empty-icon">+</div>
      <h3>Статусы ещё не созданы</h3>
      <p>
        Добавь первый статус, чтобы начать собирать рабочую доску и раскладывать дефекты по колонкам.
      </p>
      <UiButton
        v-if="canManage"
        @click="$emit('addStatus')"
      >
        Добавить статус
      </UiButton>
    </div>
  </section>
</template>

<script setup lang="ts">
import UiButton from '@/components/ui/button/UiButton.vue'
import StatusColumn from '../status-column/StatusColumn.vue'
import type { BoardStatusColumn } from '@/Ts/status'

defineProps<{
  columns: BoardStatusColumn[]
  canManage: boolean
}>()

const emit = defineEmits<{
  (e: 'addDefect'): void
  (e: 'addStatus'): void
  (e: 'statusDragStart', value: number): void
  (e: 'statusDrop', value: number): void
  (e: 'deleteStatus', value: number): void
  (e: 'statusDragEnd'): void
  (e: 'defectDragStart', value: { defectId: number; statusId: number }): void
  (e: 'defectDrop', value: { statusId: number }): void
}>()

function statusDragStart(value: number): void {
  emit('statusDragStart', value)
}

function statusDrop(value: number): void {
  emit('statusDrop', value)
}

function deleteStatus(value: number): void {
  emit('deleteStatus', value)
}

function statusDragEnd(): void {
  emit('statusDragEnd')
}

function defectDragStart(value: { defectId: number; statusId: number }): void {
  emit('defectDragStart', value)
}

function defectDrop(value: { statusId: number }): void {
  emit('defectDrop', value)
}
</script>

<style src="./board-panel.scss" lang="scss" />
