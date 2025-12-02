<template>
  <header class="bg-slate-900/90 border-b border-slate-800 backdrop-blur-xl">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">
      <!-- Izquierda: logo y título -->
      <div class="flex items-center gap-3">
        <!-- Icono / inicial -->
        <div class="flex justify-center mb-6">
          <div>
              <img
                :src="logoGrh"
                alt="Logo Gobierno Regional Huánuco"
                class="object-contain h-14 -mb-6 drop-shadow-[0_0_8px_rgba(16,185,129,0.45)]"
              />
            </div>
        </div>
        <div>
          <p class="text-sm font-semibold text-slate-100">
            Proceso de Inducción
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
            {{ auth.user?.adm_name || 'Usuario' }}
          </span>
          <span class="text-[11px] uppercase tracking-wide text-sky-400">
            {{ auth.rol_principal === 'admin' ? 'Administrador' : 'Trabajador' }}
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
import logoGrh from '../assets/logo-grh.png'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const auth = useAuthStore()

const handleLogout = () => {
  auth.logout()
  router.push({ name: 'login' })
}
</script>
