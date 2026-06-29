<template>
  <section 
    class="status-column"
    @dragover.prevent
    @drop.prevent
  >
    <header 
      class="status-column__head"
      @drop="dropStatus"
    >
      <div class="status-column__title">
        <div
          v-if="canManage"
          class="status-column__drag-handle"
          aria-label="Перетащить статус"
          title="Перетащить статус"
          role="button"
          tabindex="0"
          draggable="true"
          @dragstart.stop="startStatusDrag"
          @dragend="emit('statusDragEnd')"
        >
          <span></span>
          <span></span>
          <span></span>
          <span></span>
        </div>

        <div>
          <h3>{{ column.title }}</h3>
          <span>{{ column.description }}</span>
        </div>
      </div>
      <div class="status-column__actions">
        <strong>{{ column.items.length }}</strong>
        <button
          v-if="canManage && column.items.length === 0"
          class="status-column__delete"
          type="button"
          aria-label="Удалить статус"
          title="Удалить статус"
          @click="deleteStatus"
        >
          ×
        </button>
      </div>
    </header>

    <div 
      class="status-column__items"
      @drop="dropDefect"
    >
      <DefectCard
        v-for="item in column.items"
        :key="item.id"
        :item="item"
        draggable="true"
        @dragstart.stop="startDefectDrag($event, item.id)"
      />

      <p v-if="column.items.length === 0" class="status-column__empty">
        В этой колонке пока нет дефектов.
      </p>
    </div>
  </section>
</template>

<script setup lang="ts">
import DefectCard from '../defect-card/DefectCard.vue'
import type { BoardStatusColumn } from '@/Ts/status'

const props = defineProps<{
  column: BoardStatusColumn
  canManage: boolean
}>()

const emit = defineEmits<{
  (e: 'statusDragStart', value: number): void
  (e: 'statusDrop', value: number): void
  (e: 'deleteStatus', value: number): void
  (e: 'statusDragEnd'): void
  (e: 'defectDragStart', value: { defectId: number; statusId: number }): void
  (e: 'defectDrop', value: { statusId: number }): void
}>()

function startStatusDrag(event: DragEvent): void {
  event.dataTransfer?.setData('text/plain', String(props.column.id))

  if (event.dataTransfer) {
    event.dataTransfer.effectAllowed = 'move'
  }

  emit('statusDragStart', props.column.id)
}

function dropStatus(): void {
  emit('statusDrop', props.column.id)
}

function deleteStatus(): void {
  emit('deleteStatus', props.column.id)
}

function startDefectDrag(event: DragEvent, defectId: number): void {
  event.dataTransfer?.setData('text/plain', String(defectId))

  if (event.dataTransfer) {
    event.dataTransfer.effectAllowed = 'move'
  }

  emit('defectDragStart', {
    defectId,
    statusId: props.column.id,
  })
}

function dropDefect(): void {
  emit('defectDrop', {
    statusId: props.column.id,
  })
}
</script>

<style src="./status-column.scss" lang="scss" />
