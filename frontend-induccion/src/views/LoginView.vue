<template>
  <div
    class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-900 via-slate-950 to-sky-900 px-4"
  >
    <div class="absolute inset-0 pointer-events-none opacity-50">
      <div class="absolute -left-24 -top-24 w-72 h-72 bg-sky-500/20 rounded-full blur-3xl"></div>
      <div class="absolute -right-10 bottom-0 w-72 h-72 bg-indigo-500/20 rounded-full blur-3xl"></div>
    </div>

    <div
      class="relative z-10 w-full max-w-md bg-slate-900/80 border border-slate-800 backdrop-blur-xl rounded-2xl shadow-2xl p-8"
    >
      <!-- Header -->
      <div class="flex items-center justify-center mb-6">
        <div
          class="h-12 w-12 flex items-center justify-center rounded-2xl bg-sky-500/10 border border-sky-500/40 text-sky-400"
        >
          <span class="text-2xl font-black">I</span>
        </div>
      </div>

      <h1 class="text-2xl font-semibold text-center text-slate-50">
        Proceso de Inducción
      </h1>
      <p class="mt-2 text-center text-sm text-slate-400">
        Inicia sesión con tus credenciales institucionales
      </p>

      <!-- Error -->
      <div
        v-if="auth.error"
        class="mt-4 rounded-lg border border-red-500/40 bg-red-500/10 text-red-300 px-4 py-2 text-sm flex items-start gap-2"
      >
        <span class="mt-0.5">⚠️</span>
        <span>{{ auth.error }}</span>
      </div>

      <!-- Formulario -->
      <form class="mt-6 space-y-4" @submit.prevent="handleLogin">
        <div>
          <label class="block text-sm font-medium text-slate-200 mb-1">
            Correo electrónico
          </label>
          <div
            class="relative flex items-center rounded-xl border border-slate-700 bg-slate-900/60 focus-within:border-sky-500 focus-within:ring-2 focus-within:ring-sky-500/40 transition"
          >
            <span class="pl-3 text-slate-500">
              @
            </span>
            <input
              v-model="email"
              type="email"
              required
              autocomplete="email"
              class="w-full bg-transparent border-0 outline-none text-slate-50 text-sm px-2 py-3 rounded-xl placeholder:text-slate-500"
              placeholder="usuario@correo.com"
            />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-200 mb-1">
            Contraseña
          </label>
          <div
            class="relative flex items-center rounded-xl border border-slate-700 bg-slate-900/60 focus-within:border-sky-500 focus-within:ring-2 focus-within:ring-sky-500/40 transition"
          >
            <input
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              required
              autocomplete="current-password"
              class="w-full bg-transparent border-0 outline-none text-slate-50 text-sm px-3 py-3 rounded-xl placeholder:text-slate-500"
              placeholder="••••••••"
            />
            <button
              type="button"
              class="pr-3 text-xs text-slate-400 hover:text-slate-200 transition"
              @click="showPassword = !showPassword"
            >
              {{ showPassword ? 'Ocultar' : 'Ver' }}
            </button>
          </div>
        </div>

        <button
          type="submit"
          :disabled="auth.loading"
          class="w-full flex items-center justify-center gap-2 mt-2 bg-sky-500 hover:bg-sky-400 disabled:bg-sky-500/60 text-slate-950 font-semibold py-3 rounded-xl shadow-lg shadow-sky-500/30 transition-transform hover:-translate-y-0.5 disabled:hover:translate-y-0"
        >
          <span v-if="!auth.loading">Ingresar</span>
          <span v-else class="flex items-center gap-2">
            <span
              class="h-4 w-4 border-2 border-slate-900 border-t-transparent rounded-full animate-spin"
            ></span>
            Validando...
          </span>
        </button>
      </form>

      <!-- Footer -->
      <p class="mt-6 text-center text-xs text-slate-500">
        © {{ new Date().getFullYear() }} Sistema de Inducción • Gobierno Regional Huánuco
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const auth = useAuthStore()

const email = ref('')
const password = ref('')
const showPassword = ref(false)

const handleLogin = async () => {
  try {
    await auth.login(email.value, password.value)

    if (auth.isAdmin) {
      router.push({ name: 'admin.dashboard' })
    } else {
      router.push({ name: 'trabajador.dashboard' })
    }
  } catch (e) {
    console.error(e)
  }
}
</script>
