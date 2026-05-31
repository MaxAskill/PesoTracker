<template>
  <div class="relative">
    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm font-black text-emerald-300">
      PHP
    </span>
    <input
      :value="modelValue"
      type="text"
      inputmode="decimal"
      autocomplete="off"
      :placeholder="placeholder"
      class="money-input"
      @input="handleInput"
      @blur="handleBlur"
    />
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  placeholder: {
    type: String,
    default: '0.00'
  }
})

const emit = defineEmits(['update:modelValue', 'blur'])

const sanitizeAmount = (value) => {
  const cleaned = String(value ?? '')
    .replace(/[^\d.]/g, '')
    .replace(/(\..*)\./g, '$1')

  const [whole, decimal = ''] = cleaned.split('.')
  const normalizedWhole = whole.replace(/^0+(?=\d)/, '')

  return cleaned.includes('.')
    ? `${normalizedWhole || '0'}.${decimal.slice(0, 2)}`
    : normalizedWhole
}

const handleInput = (event) => {
  const sanitized = sanitizeAmount(event.target.value)
  event.target.value = sanitized
  emit('update:modelValue', sanitized)
}

const handleBlur = () => {
  emit('blur')
}
</script>

<style scoped>
.money-input {
  width: 100%;
  border-radius: 0.75rem;
  border: 1px solid rgb(30 41 59);
  background: rgb(2 6 23);
  padding: 0.75rem 1rem 0.75rem 3.9rem;
  color: white;
  outline: none;
  transition: 0.2s ease;
}

.money-input::placeholder {
  color: rgb(100 116 139);
}

.money-input:focus {
  border-color: rgb(16 185 129);
  box-shadow: 0 0 0 4px rgb(16 185 129 / 0.1);
}
</style>
