<template>
  <article class="stat-card" :class="`stat-card--${tone}`">
    <component :is="iconComponent" v-if="iconComponent" class="stat-card__icon" aria-hidden="true" />
    <span>{{ label }}</span>
    <strong>{{ value }}</strong>
    <small>{{ description }}</small>
  </article>
</template>

<script setup lang="ts">
import { computed, type Component } from 'vue'
import { IoCheckboxOutline, IoGridOutline, IoPeopleOutline } from 'vue-icons-plus/io'

type StatIcon = 'boards' | 'tasks' | 'members'
type StatTone = 'cyan' | 'violet'

const props = withDefaults(defineProps<{
  label: string
  value: number | string
  description: string
  icon?: StatIcon
  tone?: StatTone
}>(), {
  icon: undefined,
  tone: 'cyan',
})

const icons: Record<StatIcon, Component> = {
  boards: IoGridOutline,
  tasks: IoCheckboxOutline,
  members: IoPeopleOutline,
}

const iconComponent = computed(() => props.icon ? icons[props.icon] : null)
</script>

<style src="./ui-stat-card.scss" lang="scss" />
