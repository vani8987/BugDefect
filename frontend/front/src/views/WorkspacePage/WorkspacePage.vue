<template>
  <section class="workspace-page">
    <div class="workspace-page__intro">
      <div>
        <p class="workspace-page__eyebrow">Рабочая зона</p>
        <h1>Твои проекты и задачи</h1>
        <p class="workspace-page__description">
          Здесь появятся доски команды, активные задачи и найденные дефекты.
        </p>
      </div>

      <UiButton @click="isCreateModalOpen = true">
        Создать доску
      </UiButton>
    </div>

    <p v-if="successMessage" class="workspace-page__feedback workspace-page__feedback--success">
      {{ successMessage }}
    </p>
    <p v-if="errorMessage" class="workspace-page__feedback workspace-page__feedback--error">
      {{ errorMessage }}
    </p>

    <UiStats :columns="2">
      <UiStatCard
        label="Мои доски"
        :value="boardsCount"
        description="Доски, в которых ты участвуешь"
        icon="boards"
      />
      <UiStatCard
        v-if="false"
        label="Открытые задачи"
        :value="0"
        description="Здесь будут задачи из всех досок которые относятся к вам"
        icon="tasks"
        tone="violet"
      />
    </UiStats>

    <section class="workspace-page__boards">
      <div class="workspace-page__section-heading">
        <div>
          <p class="workspace-page__eyebrow">Проекты</p>
          <h2>Мои доски</h2>
        </div>
        <UiButton variant="secondary">Все доски</UiButton>
      </div>

      <div v-if="isLoadingBoard" class="workspace-page__loading">
        <span class="workspace-page__spinner" />
        Загрузка досок...
      </div>

      <div v-else-if="!hasBoards" class="workspace-page__empty-boards">
        <span class="workspace-page__empty-icon">+</span>
        <h3>Пока нет досок</h3>
        <p>Создай первую доску и начни собирать задачи, дефекты и участников команды.</p>
        <UiButton @click="isCreateModalOpen = true">
          Создать первую доску
        </UiButton>
      </div>

      <div v-else class="workspace-page__board-list">
        <RouterLink
          v-for="board in boards"
            :key="board.id"
            class="workspace-page__board-link"
            :to="{ name: 'board', params: { boardId: board.id } }"
        >
          <BoardCard
            :title="board.title"
            :description="board.description"
            :members-count="board.member_count"
            :role="board.role"
          />
        </RouterLink>
      </div>
    </section>

    <UiModal
      :is-open="isCreateModalOpen"
      eyebrow="Новая доска"
      title="Создание доски"
      @close="closeCreateModal"
    >
      <form class="workspace-page__board-form" @submit.prevent="submit">
        <UiInput
          v-model="boardDraft.title"
          label="Название доски"
          placeholder="Например, BugDefect"
          :maxlength="80"
          required
        />

        <UiTextarea
          v-model="boardDraft.description"
          label="Описание"
          hint="необязательно"
          placeholder="Коротко опиши, для чего нужна эта доска"
          :maxlength="255"
        />

        <p v-if="formHint" class="workspace-page__form-hint">{{ formHint }}</p>

        <div class="workspace-page__form-actions">
          <UiButton variant="secondary" @click="closeCreateModal">Отмена</UiButton>
          <UiButton
            type="submit"
            :disabled="isBoardFormInvalid || isLoadingBoard"
          >
            {{ createButtonText }}
          </UiButton>
        </div>
      </form>
    </UiModal>
  </section>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import BoardCard from '@/components/board-card/BoardCard.vue'
import UiModal from '@/components/ui/modal/UiModal.vue'
import UiButton from '@/components/ui/button/UiButton.vue'
import UiInput from '@/components/ui/input/UiInput.vue'
import UiTextarea from '@/components/ui/textarea/UiTextarea.vue'
import UiStats from '@/components/ui/stats/UiStats.vue'
import UiStatCard from '@/components/ui/stat-card/UiStatCard.vue'
import type { newBoard } from '@/Ts/board'
import { validateForm } from '@/utils/validateForm'
import { useBoardStore } from '@/stores/boardStore'
import { useStatusesStore } from '@/stores/statusesStore'

const boardStore = useBoardStore()
const statusStore = useStatusesStore()

const isCreateModalOpen = ref(false)

const formHint = ref<string | null>(null)

const boardDraft = ref<newBoard>({
  title: '',
  description: '',
})

const boards = computed(() => boardStore.boards)
const statistic = computed(() => boardStore.statistic)
const boardsCount = computed(() => statistic.value?.boards_count ?? 0)
const hasBoards = computed(() => boards.value.length > 0)
const isLoadingBoard = computed(() => boardStore.loading)

const successMessage = computed(() => boardStore.message)
const errorMessage = computed(() => boardStore.error)
const isBoardFormInvalid = computed(() => !validateForm({ title: boardDraft.value.title }))
const createButtonText = computed(() => isLoadingBoard.value ? 'Создаём...' : 'Создать доску')

function closeCreateModal(): void {
  isCreateModalOpen.value = false
  formHint.value = null
}

async function submit(): Promise<void> {
  if (isBoardFormInvalid.value) {
    formHint.value = 'Введите название доски.'
    return
  }

  const created = await boardStore.crateBoard(boardDraft.value)

  if (created) {
    boardDraft.value = { title: '', description: '' }
    closeCreateModal()
    await boardStore.getAll()
  }
}

onMounted(async () => {
  await boardStore.getAll()
})
</script>

<style src="./WorkspacePage.scss" lang="scss" />
