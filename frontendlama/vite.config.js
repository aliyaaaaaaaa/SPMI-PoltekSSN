import vue from '@vitejs/plugin-vue'
import { defineConfig } from 'vite'
import VueRouter from 'unplugin-vue-router/vite'
import Layouts from 'vite-plugin-vue-layouts'

export default defineConfig({
  plugins: [
    VueRouter(),
    Layouts(),
    vue()
  ],
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost/Kodingan TA/backend',
        changeOrigin: true,
        rewrite: (path) => path.replace(/^\/api/, '')
      }
    }
  },
})