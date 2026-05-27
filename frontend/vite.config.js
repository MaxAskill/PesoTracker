import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
  plugins: [vue(), tailwindcss()],
  server: {
      host: '0.0.0.0',
      allowedHosts: [
        'lots-christina-welcome-also.trycloudflare.com',
        '192.168.100.61',
        'photos-commissioners-joint-eggs.trycloudflare.com'
      ]
    }
})
