<template>
  <label class="ui-select">
    <span v-if="label" class="ui-select__label">
      {{ label }}
      <small v-if="hint">{{ hint }}</small>
    </span>

    <select
      class="ui-select__control"
      :value="modelValue"
      :required="required"
      :disabled="disabled"
      @change="updateValue"
    >
      <slot />
    </select>
  </label>
</template>

<script setup lang="ts">
withDefaults(defineProps<{
  modelValue: string
  label?: string
  hint?: string
  required?: boolean
  disabled?: boolean
}>(), {
  label: '',
  hint: '',
  required: false,
  disabled: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

function updateValue(event: Event): void {
  emit('update:modelValue', (event.target as HTMLSelectElement).value)
}
</script>

<style src="./ui-select.scss" lang="scss" />
