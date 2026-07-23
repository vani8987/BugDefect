<template>
  <article class="defect-card" >
    <div class="defect-card__top">
      <span class="defect-card__code">DEF-{{ item.id }}</span>
      <div class="defect-card__actions">
        <span class="defect-card__executor">{{ item.executer_name ?? `Исполнитель #${item.executer_id}` }}</span>
        <button
          v-if="canManage"
          class="defect-card__delete"
          type="button"
          aria-label="Удалить дефект"
          title="Удалить дефект"
          @click.stop="deleteDefect"
        >
          ×
        </button>
      </div>
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
import type { DeleteDefectPayload } from '../types'


const props = defineProps<{
  item: BoardDefectCard
  canManage: boolean
}>()

const emit = defineEmits<{
  (e: 'deleteDefect', value: DeleteDefectPayload): void
}>()

function deleteDefect(): void {
  emit('deleteDefect', {
    boardId: props.item.board_id,
    statusId: props.item.status_id,
    defectId: props.item.id,
  })
}
</script>

<style src="./defect-card.scss" lang="scss" />
