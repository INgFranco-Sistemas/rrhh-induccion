<template>
  <div class="min-h-screen flex flex-col bg-slate-900 text-slate-100">
    <!-- Navbar solo si está autenticado y NO estamos en /login -->
    <Navbar v-if="showNavbar" />

    <main class="flex-1">
      <RouterView />
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from './stores/auth'
import Navbar from './components/Navbar.vue'

const route = useRoute()
const auth = useAuthStore()

const showNavbar = computed(() => {
  return route.name !== 'login' && auth.isAuthenticated
})
</script>
