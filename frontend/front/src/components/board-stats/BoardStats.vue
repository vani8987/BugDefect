<template>
  <UiStats :columns="2">
    <UiStatCard
      label="Участники"
      :value="membersCount"
      :description="membersDescription"
      icon="members"
    />
    <UiStatCard
      label="Все задачи"
      :value="tasksCount"
      description="Общее количество задач на доске"
      icon="tasks"
      tone="violet"
    />
  </UiStats>
</template>

<script setup lang="ts">
import UiStats from '@/components/ui/stats/UiStats.vue'
import UiStatCard from '@/components/ui/stat-card/UiStatCard.vue'

withDefaults(defineProps<{
  membersCount: number | string
  membersDescription: string
  tasksCount?: number | string
}>(), {
  tasksCount: 0,
})
</script>
