<template>
  <div
    class="min-h-screen flex items-center justify-center
           bg-gradient-to-br from-[#022c22] via-[#02131a] to-[#16a34a]"
  >
  <!-- <div class="min-h-screen bg-gradient-to-br
       from-[#14cc8a] via-[#0fbf7f] to-[#0aa86f]
       flex items-center justify-center relative overflow-hidden"></div> -->
    <!-- halos decorativos -->
    <div
      class="pointer-events-none absolute -left-32 -top-32 h-72 w-72 rounded-full bg-emerald-500/10 blur-3xl"
    ></div>
    <div
      class="pointer-events-none absolute -right-24 bottom-0 h-80 w-80 rounded-full bg-emerald-400/10 blur-3xl"
    ></div>

    <div class="relative z-10 w-full max-w-md px-4">
      <div
        class="bg-slate-900/90 border border-slate-800 rounded-3xl shadow-2xl shadow-emerald-900/40 px-8 py-10 backdrop-blur-xl"
      >
        <!-- Icono / inicial -->
        <div class="flex justify-center mb-6">
          <div>
              <img
                :src="logoGrh"
                alt="Logo Gobierno Regional Huánuco"
                class="object-contain h-50 w-50 drop-shadow-[0_0_8px_rgba(16,185,129,0.45)]"
              />
            </div>
        </div>

        <!-- Títulos -->
        <div class="text-center mb-8">
          <h1 class="text-xl sm:text-2xl font-semibold text-slate-50 mb-1">
            Proceso de Inducción
          </h1>
          <p class="text-xs sm:text-sm text-slate-400">
            Inicia sesión con tus credenciales institucionales
          </p>
        </div>

        <!-- Formulario -->
        <form @submit.prevent="handleSubmit" class="space-y-5">
          <!-- Correo -->
          <div>
            <label class="block text-xs font-medium text-slate-200 mb-1.5">
              Correo electrónico
            </label>
            <div
              class="flex items-center rounded-xl bg-slate-950/70 border border-slate-700 focus-within:border-emerald-400 focus-within:ring-2 focus-within:ring-emerald-500/40 text-sm px-3 py-2.5 text-slate-100"
            >
              <span class="text-slate-500 text-xs mr-2">@</span>
              <input
                v-model="email"
                type="email"
                required
                autocomplete="email"
                class="w-full bg-transparent outline-none placeholder:text-slate-500"
                placeholder="usuario@correo.com"
              />
            </div>
          </div>

          <!-- Contraseña -->
          <div>
            <label class="block text-xs font-medium text-slate-200 mb-1.5">
              Contraseña
            </label>
            <div
              class="flex items-center rounded-xl bg-slate-950/70 border border-slate-700 focus-within:border-emerald-400 focus-within:ring-2 focus-within:ring-emerald-500/40 text-sm px-3 py-2.5 text-slate-100"
            >
              <input
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                required
                autocomplete="current-password"
                class="w-full bg-transparent outline-none placeholder:text-slate-500"
                placeholder="••••••••"
              />
              <button
                type="button"
                class="ml-2 text-[11px] text-slate-400 hover:text-slate-200"
                @click="togglePassword"
              >
                {{ showPassword ? 'Ocultar' : 'Ver' }}
              </button>
            </div>
          </div>

          <!-- Error -->
          <p v-if="formError" class="text-xs text-red-400">
            {{ formError }}
          </p>

          <!-- Botón -->
          <button
            type="submit"
            :disabled="auth.loading"
            class="w-full mt-2 inline-flex items-center justify-center rounded-xl bg-emerald-500 hover:bg-emerald-400 disabled:bg-emerald-500/60 text-slate-950 text-sm font-semibold py-2.5 shadow-lg shadow-emerald-500/40 transition"
          >
            <span v-if="!auth.loading">Ingresar</span>
            <span v-else class="flex items-center gap-2">
              <span
                class="h-4 w-4 rounded-full border-2 border-slate-900 border-t-transparent animate-spin"
              ></span>
              Ingresando...
            </span>
          </button>
        </form>

        <!-- Pie -->
        <div class="mt-8 text-center">
          <p class="text-[11px] text-slate-500">
            © 2025 Sistema de Inducción ·
            <span class="text-emerald-300">Gobierno Regional Huánuco</span>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import logoGrh from '../assets/logo-grh.png'
import { ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const auth = useAuthStore()

const email = ref('')
const password = ref('')
const showPassword = ref(false)
const formError = ref('')

const togglePassword = () => {
  showPassword.value = !showPassword.value
}

watch(
  () => auth.error,
  (value) => {
    if (value) {
      formError.value = value
    }
  },
)

const handleSubmit = async () => {
  formError.value = ''
  try {
    await auth.login(email.value, password.value)
    // Redirigir según el rol
    if (auth.isAdmin) {
      router.push({ name: 'admin.dashboard' })
    } else {
      router.push({ name: 'trabajador.dashboard' })
    }
  } catch (error) {
    // El mensaje ya viene del store (auth.error)
    if (!formError.value) {
      formError.value = 'No se pudo iniciar sesión. Verifique sus credenciales.'
    }
  }
}
</script>
