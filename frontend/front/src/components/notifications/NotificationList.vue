<template>
  <section class="notification-list" aria-label="Уведомления">
    <div class="notification-list__heading">
      <div>
        <span>Уведомления</span>
        <small>{{ unreadLabel }}</small>
      </div>
      <span v-if="unreadCount > 0" class="notification-list__dot" aria-hidden="true"></span>
    </div>

    <div class="notification-list__items">
      <article
        v-for="notification in notifications"
        :key="notification.id"
        class="notification-list__item"
        :class="`notification-list__item--${notification.type}`"
      >
        <span class="notification-list__icon">
          <IoPersonAddOutline v-if="notification.type === 'invite'" />
          <IoNotificationsOutline v-else />
        </span>

        <div class="notification-list__content">
          <div class="notification-list__top">
            <strong>{{ notification.title }}</strong>
            <span v-if="!notification.is_read" class="notification-list__status">Новое</span>
          </div>

          <p>{{ notification.description }}</p>

          <p
            v-if="notification.type === 'invite' && processingId === notification.id"
            class="notification-list__process"
          >
            Обрабатываем приглашение...
          </p>

          <p
            v-else-if="notification.type === 'invite' && notification.data?.status !== 'pending'"
            class="notification-list__process"
          >
            {{ getInviteStatusText(notification.data?.status) }}
          </p>

          <div
            v-else-if="notification.type === 'invite'"
            class="notification-list__actions"
          >
            <button
              type="button"
              class="notification-list__button notification-list__button--accept"
              @click="$emit('accept', notification.id)"
            >
              Принять
            </button>
            <button
              type="button"
              class="notification-list__button"
              @click="$emit('decline', notification.id)"
            >
              Отклонить
            </button>
          </div>
        </div>
      </article>

      <p v-if="notifications.length === 0" class="notification-list__empty">
        Уведомлений пока нет.
      </p>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { IoNotificationsOutline, IoPersonAddOutline } from 'vue-icons-plus/io'
import type { notificationInvite } from '@/Ts/notification'

const props = defineProps<{
  notifications: notificationInvite[]
  processingId?: number | null
}>()

defineEmits<{
  accept: [id: number]
  decline: [id: number]
}>()

const unreadCount = computed(() => props.notifications.filter((notification) => !notification.is_read).length)
const unreadLabel = computed(() => unreadCount.value === 0 ? 'Новых нет' : `${unreadCount.value} новых`)

function getInviteStatusText(status: 'pending' | 'accepted' | 'declined' | undefined): string {
  if (status === 'accepted') return 'Вы приняли приглашение'
  if (status === 'declined') return 'Вы отклонили приглашение'

  return 'Приглашение уже обработано'
}
</script>

<style src="./notification-list.scss" lang="scss" />
