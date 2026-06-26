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

          <div v-if="notification.type === 'invite'" class="notification-list__actions">
            <button type="button" class="notification-list__button notification-list__button--accept" @click="$emit('accept', notification.id)">
              Принять
            </button>
            <button type="button" class="notification-list__button" @click="$emit('decline', notification.id)">
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
}>()

defineEmits<{
  accept: [id: number]
  decline: [id: number]
}>()

const unreadCount = computed(() => props.notifications.filter((notification) => !notification.is_read).length)
const unreadLabel = computed(() => unreadCount.value === 0 ? 'Новых нет' : `${unreadCount.value} новых`)
</script>

<style src="./notification-list.scss" lang="scss" />
