<template>
  <section 
    class="status-column"
    @dragover.prevent
    @drop.prevent="dropColumn"
  >
    <header class="status-column__head">
      <div class="status-column__title">
        <div
          v-if="canManage"
          class="status-column__drag-handle"
          aria-label="Перетащить статус"
          title="Перетащить статус"
          role="button"
          tabindex="0"
          draggable="true"
          @dragstart.stop="startDrag"
          @dragend="emit('dragEnd')"
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
import type { BoardStatusColumn } from '@/Ts/status'

const props = defineProps<{
  column: BoardStatusColumn
  canManage: boolean
}>()

const emit = defineEmits<{
  (e: 'startDrag', value: number): void
  (e: 'dropColumn', value: number): void
  (e: 'dragEnd'): void
}>()

function startDrag(event: DragEvent): void {
  event.dataTransfer?.setData('text/plain', String(props.column.id))

  if (event.dataTransfer) {
    event.dataTransfer.effectAllowed = 'move'
  }

  emit('startDrag', props.column.id)
}

function dropColumn(): void {
  emit('dropColumn', props.column.id)
}
</script>

<style src="./status-column.scss" lang="scss" />
