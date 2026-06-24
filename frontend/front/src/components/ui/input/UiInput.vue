<template>
  <label class="ui-input">
    <span v-if="label" class="ui-input__label">
      {{ label }}
      <small v-if="hint">{{ hint }}</small>
    </span>

    <input
      class="ui-input__control"
      :value="modelValue ?? ''"
      :type="type"
      :placeholder="placeholder"
      :maxlength="maxlength"
      :required="required"
      :disabled="disabled"
      @input="updateValue"
    />

    <span v-if="error" class="ui-input__error">{{ error }}</span>
  </label>
</template>

<script setup lang="ts">
withDefaults(defineProps<{
  modelValue?: string
  label?: string
  hint?: string
  error?: string
  type?: string
  placeholder?: string
  maxlength?: number
  required?: boolean
  disabled?: boolean
}>(), {
  type: 'text',
  placeholder: '',
  maxlength: undefined,
  required: false,
  disabled: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

function updateValue(event: Event): void {
  emit('update:modelValue', (event.target as HTMLInputElement).value)
}
</script>

<style src="./ui-input.scss" lang="scss" />
