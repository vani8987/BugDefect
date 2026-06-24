<template>
  <label class="ui-textarea">
    <span v-if="label" class="ui-textarea__label">
      {{ label }}
      <small v-if="hint">{{ hint }}</small>
    </span>

    <textarea
      class="ui-textarea__control"
      :value="modelValue ?? ''"
      :rows="rows"
      :placeholder="placeholder"
      :maxlength="maxlength"
      :required="required"
      :disabled="disabled"
      @input="updateValue"
    />

    <span v-if="error" class="ui-textarea__error">{{ error }}</span>
  </label>
</template>

<script setup lang="ts">
withDefaults(defineProps<{
  modelValue?: string
  label?: string
  hint?: string
  error?: string
  placeholder?: string
  rows?: number
  maxlength?: number
  required?: boolean
  disabled?: boolean
}>(), {
  placeholder: '',
  rows: 4,
  maxlength: undefined,
  required: false,
  disabled: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

function updateValue(event: Event): void {
  emit('update:modelValue', (event.target as HTMLTextAreaElement).value)
}
</script>

<style src="./ui-textarea.scss" lang="scss" />
