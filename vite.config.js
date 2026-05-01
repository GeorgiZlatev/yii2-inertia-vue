import { defineConfig } from "vite";
import tailwindcss from "@tailwindcss/vite";
import vue from "@vitejs/plugin-vue";

export default defineConfig({
  plugins: [tailwindcss(), vue()],
  build: {
    manifest: true,
    copyPublicDir: false,
    outDir: "public/build",
    rollupOptions: {
      input: "resources/js/app.js",
    },
  },
  // server: {
  //   origin: "http://localhost:5173",
  // },
  server: {
    host: true,          // или '0.0.0.0'
    port: 5173,
    strictPort: true,
    hmr: {
      host: 'localhost',
    },
  },
});
