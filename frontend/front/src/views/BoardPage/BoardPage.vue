<template>
  <section class="board-page">
    <RouterLink class="board-page__back-link" :to="{ name: 'workspace' }">
      ← Все доски
    </RouterLink>

    <div class="board-page__intro">
      <div>
        <p class="board-page__eyebrow">Доска #{{ boardView.id }}</p>
        <h1>{{ boardView.title }}</h1>
        <p class="board-page__description">
          {{ boardView.description }}
        </p>
      </div>

      <div class="board-page__actions">
        <span class="board-page__role">
          {{ boardView.role }}
        </span>

        <UiButton variant="secondary" v-if="boardStore.currentBoard?.role === 'admin'" @click="isAddMemberModalOpen = true">
          Добавить участника
        </UiButton>

        <UiButton
          v-if="boardStore.currentBoard?.role === 'admin'"
          class="board-page__delete-board"
          variant="secondary"
          @click="deleteBoard"
        >
          Удалить доску
        </UiButton>

        <UiDropdown v-model="isParticipantsOpen" label="Участники">
          <p class="board-page__eyebrow">Команда</p>
          <h2>Участники</h2>
          <p class="board-page__dropdown-description">
            {{ participantsView.description }}
          </p>

          <div v-if="participantsView.showList" class="board-page__members-list">
            <article v-for="member in participantsView.items" :key="member.id" class="board-page__member">
              <span class="board-page__member-avatar">{{ member.initial }}</span>
              <div class="board-page__member-info">
                <strong>{{ member.name }}</strong>
                <small>{{ member.email }}</small>
              </div>
              <span class="board-page__member-role">{{ member.role }}</span>
              <button
                v-if="boardStore.currentBoard?.role === 'admin' && member.id !== authStore.user?.id"
                class="board-page__member-remove"
                type="button"
                aria-label="Удалить участника"
                @click.stop="deleteMember(member.id)"
              >
                Удалить
              </button>
            </article>
          </div>

          <p v-else class="board-page__members-empty">{{ participantsView.emptyText }}</p>
        </UiDropdown>
      </div>
    </div>

    <p v-if="errorMessage" class="board-page__request-error" role="alert">
      {{ errorMessage }}
    </p>

    <UiStats :columns="2">
      <UiStatCard
        label="Участники"
        :value="membersCount"
        :description="membersDescription"
        icon="members"
      />
      <UiStatCard
        label="Все задачи"
        :value="0"
        description="Общее количество задач на доске"
        icon="tasks"
        tone="violet"
      />
    </UiStats>

    <div class="board-page__layout">
      <div v-if="isInitialStatusLoading" class="workspace-page__loading">
        <span class="workspace-page__spinner" />
        Загрузка статусов...
      </div>

      <BoardPanel
        v-else
        :columns="statusStore.boardColumns"
        :can-manage="boardStore.currentBoard?.role === 'admin'"
        @add-defect="isCreateDefectModalOpen = true"
        @add-status="isCreateStatusModalOpen = true"
        @drag-start="startDrag"
        @drop-column="dropColumn"
        @drag-end="dragEnd"
      />
    </div>

    <UiModal
      :is-open="isCreateDefectModalOpen"
      eyebrow="Новый дефект"
      title="Создание дефекта"
      @close="closeCreateDefectModal"
    >
      <form class="board-page__form" @submit.prevent="createStatus">
        <UiInput
          v-model="defectDraft.title"
          label="Название"
          placeholder="Например, не открывается список участников"
          :maxlength="120"
          required
        />
        <UiTextarea
          v-model="defectDraft.description"
          label="Описание"
          hint="необязательно"
          placeholder="Опиши проблему, шаги воспроизведения и ожидаемый результат"
          :maxlength="1000"
        />
        <UiSelect v-model="defectDraft.status" label="Начальный статус">
          <option value="new">Новый</option>
          <option value="in_progress">В работе</option>
        </UiSelect>
        <div class="board-page__form-actions">
          <UiButton variant="secondary" @click="closeCreateDefectModal">Отмена</UiButton>
          <UiButton type="submit">Создать дефект</UiButton>
        </div>
      </form>
    </UiModal>

    <UiModal
      :is-open="isAddMemberModalOpen"
      eyebrow="Команда"
      title="Добавить участника"
      @close="closeAddMemberModal"
    >
      <form class="board-page__form" @submit.prevent>
        <UiInput
          v-model="memberDraft.email"
          label="Email пользователя"
          type="email"
          placeholder="name@example.com"
          required
        />
        <UiSelect v-model="memberDraft.role" label="Роль на доске">
          <option value="developer">Разработчик</option>
          <option value="guest">Гость</option>
          <option value="admin">Администратор</option>
        </UiSelect>
        <p class="board-page__form-note">
          Пользователю будет отправлено приглашение на доску.
        </p>
        <div class="board-page__form-actions">
          <UiButton variant="secondary" @click="closeAddMemberModal">Отмена</UiButton>
          <UiButton type="submit" @click="inviteStore.sendInvite(boardId, memberDraft)">Добавить</UiButton>
        </div>
      </form>
    </UiModal>

    <UiModal
      :is-open="isCreateStatusModalOpen"
      eyebrow="Новый статус"
      title="Добавить статус"
      @close="closeCreateStatusModal"
    >
      <form class="board-page__form" @submit.prevent>
        <UiInput
          v-model="statusDraft.title"
          label="Название статуса"
          placeholder="Например, Заблокировано"
          :maxlength="80"
          required
        />
        <UiTextarea
          v-model="statusDraft.description"
          label="Описание"
          hint="необязательно"
          placeholder="Коротко опиши, какие дефекты будут попадать в эту колонку"
          :maxlength="255"
        />
        <div class="board-page__form-actions">
          <UiButton variant="secondary" @click="closeCreateStatusModal">Отмена</UiButton>
          <UiButton type="submit" @click="createStatus" :disabled="statusStore.loading">Создать статус</UiButton>
        </div>
      </form>
    </UiModal>
  </section>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import UiDropdown from '@/components/ui/dropdown/UiDropdown.vue'
import UiButton from '@/components/ui/button/UiButton.vue'
import UiInput from '@/components/ui/input/UiInput.vue'
import UiModal from '@/components/ui/modal/UiModal.vue'
import UiSelect from '@/components/ui/select/UiSelect.vue'
import UiStats from '@/components/ui/stats/UiStats.vue'
import UiStatCard from '@/components/ui/stat-card/UiStatCard.vue'
import UiTextarea from '@/components/ui/textarea/UiTextarea.vue'
import BoardPanel from '@/components/board-workspace/board-panel/BoardPanel.vue'
import { useBoardStore } from '@/stores/boardStore'
import { useAuthStore } from '@/stores/AuthStore'
import { useInviteStore } from '@/stores/InviteStore'
import { useStatusesStore } from '@/stores/statusesStore'
import { getRoleLabel } from '@/Ts/role'
import router from '@/router'

const inviteStore = useInviteStore()
const boardStore = useBoardStore()
const authStore = useAuthStore()
const statusStore = useStatusesStore()

const route = useRoute()
const boardId = computed(() => Number(route.params.boardId))
const isParticipantsOpen = ref(false)
const isCreateDefectModalOpen = ref(false)
const isAddMemberModalOpen = ref(false)
const isCreateStatusModalOpen = ref(false)
const isInitialStatusLoading = ref(false)

const fromColumn = ref<number | null>(null)
const toColumn = ref<number | null>(null)
const defectDraft = ref({
  title: '',
  description: '',
  status: 'new',
})
const statusDraft = ref({
  title: '',
  description: '',
})
const memberDraft = ref({
  email: '',
  role: 'developer',
})
const isLoading = computed(() => boardStore.loading)
const errorMessage = computed(() => boardStore.error || statusStore.error)
const membersCount = computed(() => boardStore.currentBoard?.member_count ?? '—')
const membersDescription = computed(() => isLoading.value ? 'Загружаем данные доски...' : 'Участники этой доски')
const participantsView = computed(() => {
  const items = boardStore.members.map((member) => ({
    ...member,
    initial: member.name.slice(0, 1).toUpperCase(),
    role: getRoleLabel(member.role),
  }))

  return {
    items,
    showList: items.length > 0,
    description: isLoading.value
      ? 'Загружаем участников...'
      : `В этой доске ${items.length} ${items.length === 1 ? 'участник' : 'участников'}.`,
    emptyText: isLoading.value ? 'Список участников загружается...' : 'В этой доске пока нет участников.',
  }
})
const boardView = computed(() => {
  const board = boardStore.currentBoard

  return {
    id: boardId.value,
    title: board?.title ?? 'Загрузка доски...',
    description: board?.description || 'Описание для этой доски пока не добавлено.',
    role: board ? getRoleLabel(board.role) : 'Роль загружается',
  }
})

const startDrag = (id: number): void => {
  fromColumn.value = id
}

const resetDrag = (): void => {
  fromColumn.value = null
  toColumn.value = null
}

const dragEnd = (): void => {
  resetDrag()
}

const dropColumn = async (id: number): Promise<void> => {
  toColumn.value = id

  if (fromColumn.value === null || toColumn.value === fromColumn.value) {
    resetDrag()
    return
  }

  const toIndex = statusStore.boardColumns.findIndex((column) => column.id === toColumn.value)
  const fromIndex = statusStore.boardColumns.findIndex((column) => column.id === fromColumn.value)

  if (fromIndex === -1 || toIndex === -1) {
    resetDrag()
    return
  }

  const savedColumns = statusStore.boardColumns.map((column) => ({
    ...column,
    items: [...column.items],
  }))
  const [fromColumnObj] = statusStore.boardColumns.splice(fromIndex, 1)

  if (fromColumnObj === undefined) {
    resetDrag()
    return
  }

  statusStore.boardColumns.splice(toIndex, 0, fromColumnObj)

  statusStore.boardColumns = statusStore.boardColumns.map((column, index) => ({
    ...column,
    position: index + 1,
  }))

  const statusPositions = statusStore.boardColumns.map((status) => ({
    id: status.id,
    position: status.position,
  }))

  resetDrag()

  const isUpdated = await statusStore.updatePosition(boardId.value, { statuses: statusPositions })

  if (!isUpdated) {
    statusStore.boardColumns = savedColumns
  }
}

async function loadBoard(): Promise<void> {
  isInitialStatusLoading.value = true

  try {
    await boardStore.getOneBoard(boardId.value)
    await boardStore.getBoardMembers(boardId.value)
    await statusStore.getAll(boardId.value)
  } finally {
    isInitialStatusLoading.value = false
  }
}

function closeCreateDefectModal(): void {
  isCreateDefectModalOpen.value = false
  defectDraft.value = { title: '', description: '', status: 'new' }
}

function closeAddMemberModal(): void {
  isAddMemberModalOpen.value = false
  memberDraft.value = { email: '', role: 'developer' }
}

function closeCreateStatusModal(): void {
  isCreateStatusModalOpen.value = false
  statusDraft.value = { title: '', description: '' }
}

async function createStatus(): Promise<void> {
  const isCreated = await statusStore.createStatus(boardId.value, {
    ...statusDraft.value,
    position: statusStore.boardColumns.length + 1,
  })

  if (isCreated) {
    closeCreateStatusModal()
  }
}

async function deleteBoard(): Promise<void> {
  await boardStore.deleteBoard(boardId.value)
  router.push({name: 'workspace'})
}

async function deleteMember(memberId: number): Promise<void> {
  await boardStore.deleteUserInBoarde(boardId.value, memberId)
  await boardStore.getBoardMembers(boardId.value)
}

onMounted(loadBoard)

watch(boardId, () => {
  isParticipantsOpen.value = false
  void loadBoard()
})
</script>

<style src="./BoardPage.scss" lang="scss" />
