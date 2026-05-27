<script setup>
import { onMounted } from 'vue'
import { preloadAuthenticatedData, warmBackend } from './services/preload'

onMounted(() => {
  warmBackend()
  preloadAuthenticatedData()
})
</script>

<template>
  <router-view v-slot="{ Component, route }">
    <KeepAlive>
      <component
        :is="Component"
        v-if="route.meta.keepAlive"
        :key="route.name"
      />
    </KeepAlive>

    <component
      :is="Component"
      v-if="!route.meta.keepAlive"
      :key="route.name || route.path"
    />
  </router-view>
</template>
