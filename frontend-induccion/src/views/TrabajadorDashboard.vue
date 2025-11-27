<template>
  <div class="min-h-[calc(100vh-56px)] bg-slate-950">
    <div class="max-w-4xl mx-auto px-4 py-8">
      <h1 class="text-2xl font-semibold text-slate-50 mb-2">
        Bienvenido al Curso de Inducción
      </h1>
      <p class="text-sm text-slate-400 mb-6">
        Completa todos los videos en orden y firma tu declaración jurada al finalizar.
      </p>

      <!-- Tarjeta comenzar curso -->
      <div class="bg-slate-900/80 border border-sky-500/30 rounded-2xl p-5 shadow-lg mb-4">
        <div class="flex items-center justify-between gap-3">
          <div>
            <p class="text-sm font-semibold text-slate-50">
              Comenzar curso de inducción
            </p>
            <p class="text-xs text-slate-400">
              Accede a la lista de videos y síguelos en el orden indicado.
            </p>
          </div>
          <RouterLink
            :to="{ name: 'trabajador.videos' }"
            class="inline-flex items-center rounded-xl bg-sky-500 hover:bg-sky-400 text-slate-950 text-xs font-semibold px-4 py-2 shadow-lg shadow-sky-500/30"
          >
            Ver videos →
          </RouterLink>
        </div>
      </div>

      <!-- Progreso del curso -->
      <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 shadow-lg mb-4">
        <div class="flex items-center justify-between mb-2">
          <p class="text-sm font-semibold text-slate-50">
            Progreso del curso
          </p>
          <p class="text-xs text-slate-400">
            {{ curso.completados }} / {{ curso.totalVideos }} videos completados
          </p>
        </div>

        <div class="w-full h-2.5 rounded-full bg-slate-800 overflow-hidden mb-2">
          <div
            class="h-full bg-emerald-500 transition-all"
            :style="{ width: progresoPorcentaje + '%' }"
          ></div>
        </div>

        <p class="text-xs text-slate-400">
          {{ mensajeProgreso }}
        </p>
      </div>

      <!-- Declaración jurada -->
      <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 shadow-lg">
        <p class="text-sm font-semibold text-slate-50 mb-1">
          Declaración jurada de culminación
        </p>

        <template v-if="curso.declaracionFirmada">
          <p class="text-xs text-emerald-400 mb-2">
            Ya has firmado la declaración jurada. ✅
          </p>
          <p class="text-xs text-slate-400">
            Gracias por completar el curso de inducción.
          </p>
        </template>

        <template v-else>
          <p class="text-xs text-slate-400 mb-3">
            La declaración se habilita únicamente cuando completes todos los videos del curso.
          </p>

          <button
            v-if="curso.puedeFirmar"
            @click="firmarDeclaracion"
            :disabled="firmando"
            class="inline-flex items-center rounded-xl bg-emerald-500 hover:bg-emerald-400 disabled:bg-emerald-500/60 text-slate-950 text-xs font-semibold px-4 py-2 shadow-lg shadow-emerald-500/30"
          >
            <span v-if="!firmando">Firmar declaración jurada</span>
            <span v-else>Firmando...</span>
          </button>

          <p v-else class="text-xs text-amber-400">
            Aún no puedes firmar. Completa primero todos los videos.
          </p>

          <p v-if="mensajeFirma" :class="claseMensajeFirma" class="mt-2 text-xs">
            {{ mensajeFirma }}
          </p>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useCursoStore } from '../stores/curso'

const curso = useCursoStore()
const firmando = ref(false)
const mensajeFirma = ref('')
const tipoMensajeFirma = ref('') // 'ok' | 'error'

const progresoPorcentaje = computed(() => {
  if (!curso.totalVideos) return 0
  return Math.round((curso.completados / curso.totalVideos) * 100)
})

const mensajeProgreso = computed(() => {
  if (!curso.totalVideos) return 'Aún no hay videos registrados en el curso.'
  if (curso.completados === 0) return 'Todavía no has iniciado el curso.'
  if (curso.completados < curso.totalVideos) {
    return 'Continúa viendo los videos en el orden indicado hasta completar el curso.'
  }
  return 'Has completado todos los videos. Ahora puedes firmar la declaración jurada.'
})

const claseMensajeFirma = computed(() =>
  tipoMensajeFirma.value === 'ok' ? 'text-emerald-400' : 'text-red-400',
)

const firmarDeclaracion = async () => {
  firmando.value = true
  mensajeFirma.value = ''
  tipoMensajeFirma.value = ''
  try {
    await curso.firmarDeclaracion({ acepta: true })
    mensajeFirma.value = 'Declaración firmada correctamente.'
    tipoMensajeFirma.value = 'ok'
  } catch (error) {
    console.error(error)
    mensajeFirma.value =
      error.response?.data?.message || 'No se pudo firmar la declaración.'
    tipoMensajeFirma.value = 'error'
  } finally {
    firmando.value = false
  }
}

onMounted(() => {
  curso.fetchEstado()
})
</script>
