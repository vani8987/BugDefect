<template>
  <div class="ui-dropdown">
    <button
      class="ui-dropdown__toggle"
      type="button"
      :aria-expanded="modelValue"
      @click="emit('update:modelValue', !modelValue)"
    >
      {{ label }}
      <span class="ui-dropdown__arrow" :class="{ 'ui-dropdown__arrow--open': modelValue }">⌄</span>
    </button>

    <Transition name="ui-dropdown-menu">
      <div v-if="modelValue" class="ui-dropdown__menu">
        <slot />
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  label: string
  modelValue: boolean
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
}>()
</script>

<style src="./ui-dropdown.scss" lang="scss" />
