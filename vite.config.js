import fs from 'fs'
import path from 'path'
import { defineConfig } from 'vite'
import { fileURLToPath } from 'url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))

const htmlEntries = fs
  .readdirSync(__dirname)
  .filter((f) => f.endsWith('.html'))
  .reduce((acc, f) => {
    acc[path.basename(f, '.html')] = path.resolve(__dirname, f)
    return acc
  }, {})

export default defineConfig({
  appType: 'mpa',
  server: {
    port: 5173,
    open: true,
  },
  build: {
    rollupOptions: {
      input: htmlEntries,
    },
  },
})
