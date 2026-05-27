import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import { VitePWA } from 'vite-plugin-pwa'

export default defineConfig({
  plugins: [
    vue(),
    tailwindcss(),

    VitePWA({
        registerType: 'autoUpdate',
    
        manifest: {
          name: 'PesoTracker',
          short_name: 'PesoTracker',
          description: 'AI-powered expense tracking and budget management app',
          theme_color: '#020617',
          background_color: '#020617',
          display: 'standalone',
          orientation: 'portrait',
          start_url: '/',
          icons: [
            {
              src: '/pwa-192x192.png',
              sizes: '192x192',
              type: 'image/png'
            },
            {
              src: '/pwa-512x512.png',
              sizes: '512x512',
              type: 'image/png'
            }
          ]
        },
    
        workbox: {
          navigateFallback: '/index.html'
        }
      }),
      
  ],
  server: {
      host: '0.0.0.0',
      allowedHosts: [
        'lots-christina-welcome-also.trycloudflare.com',
        '192.168.100.61',
        'photos-commissioners-joint-eggs.trycloudflare.com'
      ]
    }
})
