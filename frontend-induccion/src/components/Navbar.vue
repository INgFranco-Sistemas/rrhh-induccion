<template>
  <header class="bg-slate-900/90 border-b border-slate-800 backdrop-blur-xl">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">
      <!-- Izquierda: logo y título -->
      <div class="flex items-center gap-3">
        <div
          class="h-9 w-9 flex items-center justify-center rounded-xl bg-sky-500/10 border border-sky-500/50 text-sky-400 font-bold text-lg"
        >
          I
        </div>
        <div>
          <p class="text-sm font-semibold text-slate-100">
            Sistema de Inducción
          </p>
          <p class="text-xs text-slate-400">
            Gobierno Regional Huánuco
          </p>
        </div>
      </div>

      <!-- Derecha: usuario y salir -->
      <div class="flex items-center gap-4">
        <div class="hidden sm:flex flex-col items-end text-right">
          <span class="text-sm font-medium text-slate-100">
            {{ auth.user?.name || 'Usuario' }}
          </span>
          <span class="text-[11px] uppercase tracking-wide text-sky-400">
            {{ auth.user?.role === 'admin' ? 'Administrador' : 'Trabajador' }}
          </span>
        </div>

        <button
          @click="handleLogout"
          class="inline-flex items-center gap-1 rounded-lg border border-slate-700 bg-slate-800/80 px-3 py-1.5 text-xs font-medium text-slate-100 hover:bg-slate-700 transition"
        >
          <span>Salir</span>
        </button>
      </div>
    </div>
  </header>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const auth = useAuthStore()

const handleLogout = () => {
  auth.logout()
  router.push({ name: 'login' })
}
</script>
