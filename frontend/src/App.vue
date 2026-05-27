<script setup>
import { onMounted } from 'vue'
import { preloadAuthenticatedData, warmBackend } from './services/preload'

onMounted(() => {
  warmBackend()
  preloadAuthenticatedData()
})
</script>

<template>
  <!-- <HelloWorld /> -->
  <router-view />
</template>
