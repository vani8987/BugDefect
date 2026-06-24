<template>
  <div class="ui-stats" :class="`ui-stats--${columns}`" aria-label="Статистика">
    <slot />
  </div>
</template>

<script setup lang="ts">
withDefaults(defineProps<{
  columns?: 2 | 3
}>(), {
  columns: 2,
})
</script>

<style src="./ui-stats.scss" lang="scss" />
