<template>
  <Teleport to="body">
    <Transition name="modal-window">
      <div v-if="isOpen" class="modal-window" role="presentation" @click.self="emit('close')">
        <section
          class="modal-window__content"
          role="dialog"
          aria-modal="true"
          :aria-labelledby="titleId"
        >
          <div class="modal-window__heading">
            <div>
              <p v-if="eyebrow" class="modal-window__eyebrow">{{ eyebrow }}</p>
              <h2 :id="titleId">{{ title }}</h2>
            </div>

            <button class="modal-window__close" type="button" aria-label="Закрыть окно" @click="emit('close')">
              ×
            </button>
          </div>

          <div class="modal-window__body">
            <slot />
          </div>
        </section>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
defineProps<{
  isOpen: boolean
  title: string
  eyebrow?: string
}>()

const emit = defineEmits<{
  close: []
}>()

const titleId = `modal-window-title-${Math.random().toString(36).slice(2, 9)}`
</script>

<style src="./ui-modal.scss" lang="scss" />
