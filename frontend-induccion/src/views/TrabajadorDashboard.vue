<template>
  <div class="min-h-[calc(100vh-56px)] bg-slate-950 justify-center
           bg-gradient-to-br from-[#022c22] via-[#02131a] to-[#16a34a]">
    <div class="max-w-5xl mx-auto px-4 py-8 space-y-6">

      <!-- ENCABEZADO -->
      <header class="space-y-1">
        <h1 class="text-2xl font-semibold">Bienvenido al Proceso de Inducción</h1>
        <p class="text-sm text-slate-400">
          Completa todos los videos en orden y firma tu declaración jurada al finalizar.
        </p>
      </header>

      <!-- TARJETAS PRINCIPALES -->
      <section class="space-y-4">

        <!-- Card: Comenzar curso -->
        <div
          class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4 shadow-lg"
        >
          <div class="space-y-1">
            <p class="text-xs font-semibold text-sky-400 uppercase tracking-wide">
              Proceso de inducción
            </p>
            <p class="text-sm text-slate-50">
              Comenzar Proceso de inducción
            </p>
            <p class="text-xs text-slate-400">
              Accede a la lista de videos y síguelos en el orden indicado.
            </p>
          </div>

          <RouterLink
            :to="{ name: 'trabajador.videos' }"
            class="inline-flex items-center justify-center rounded-full bg-sky-500 hover:bg-sky-400
                   text-slate-950 text-xs font-semibold px-4 py-2 shadow-lg shadow-sky-500/30"
          >
            Ver videos →
          </RouterLink>
          <!-- NOTA: ajusta el name de la ruta arriba según tu router -->
        </div>

        <!-- Card: Progreso del curso -->
        <div
          class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 shadow-lg space-y-3"
        >
          <div class="flex items-center justify-between gap-3">
            <p class="text-sm font-semibold text-slate-50">
              Progreso del Proceso
            </p>
            <p class="text-xs text-slate-400">
              {{ curso.completados }} / {{ curso.totalVideos }} videos completados
            </p>
          </div>

          <!-- Barra de progreso -->
          <div class="w-full h-2 rounded-full bg-slate-800 overflow-hidden">
            <div
              class="h-full bg-emerald-500 transition-all duration-500"
              :style="{ width: progresoPorcentaje + '%' }"
            ></div>
          </div>

          <p class="text-xs text-slate-400">
            {{ mensajeProgreso }}
          </p>
        </div>

        <!-- Card: Declaración jurada -->
        <div
          class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 shadow-lg"
        >
          <p class="text-sm font-semibold text-slate-50 mb-1">
            Declaración jurada de culminación
          </p>
          <p class="text-xs text-slate-400 mb-3">
            La declaración se habilita únicamente cuando completes todos los videos del curso.
          </p>

          <!-- SI YA FIRMÓ -->
          <template v-if="curso.declaracionFirmada">
            <p class="text-xs text-emerald-400 mb-2">
              Ya has firmado la declaración jurada. ✅
            </p>
            <p v-if="curso.declaracion?.firmado_at" class="text-[11px] text-slate-400 mb-1">
              Fecha de firma:
              {{ new Date(curso.declaracion.firmado_at).toLocaleString() }}
            </p>
            <a
              v-if="curso.plantillaDeclaracion?.url"
              :href="curso.plantillaDeclaracion.url"
              target="_blank"
              rel="noopener"
              class="text-xs text-sky-400 hover:text-sky-300"
            >
              Ver declaración jurada (PDF) →
            </a>
          </template>

          <!-- SI NO HA FIRMADO -->
          <template v-else>
            <button
              :disabled="!curso.puedeFirmar || !curso.plantillaDeclaracion?.url"
              @click="abrirModalDeclaracion"
              class="inline-flex items-center rounded-full bg-emerald-500 hover:bg-emerald-400
                     disabled:bg-emerald-500/40 text-slate-950 text-xs font-semibold px-5 py-2
                     shadow-lg shadow-emerald-500/30"
            >
              DECLARACIÓN JURADA
            </button>

            <p v-if="!curso.plantillaDeclaracion?.url" class="mt-2 text-xs text-amber-400">
              El administrador aún no ha cargado la declaración jurada en PDF.
            </p>

            <p v-else-if="!curso.puedeFirmar" class="mt-2 text-xs text-amber-400">
              Completa primero todos los videos para poder firmar la declaración.
            </p>
          </template>
        </div>
      </section>
    </div>

    <!-- MODAL FLOTANTE DECLARACIÓN -->
    <div
      v-if="showDeclaracionModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
      @click.self="cerrarModalDeclaracion"
    >
      <div
        class="w-full max-w-4xl max-h-[90vh] bg-slate-900 border border-slate-700 rounded-2xl
               shadow-2xl overflow-hidden flex flex-col"
      >
        <!-- Header modal -->
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-800">
          <h2 class="text-sm font-semibold text-slate-50">
            Declaración jurada del curso de inducción
          </h2>
          <button
            @click="cerrarModalDeclaracion"
            class="text-xs text-slate-400 hover:text-slate-200"
          >
            ✕ Cerrar
          </button>
        </div>

        <!-- Cuerpo modal -->
        <div class="flex-1 flex flex-col md:flex-row">
          <!-- PDF -->
          <div class="flex-1 min-h-[800px] border-b md:border-b-0 md:border-r border-slate-800">
            <iframe
              v-if="curso.plantillaDeclaracion?.url"
              :src="curso.plantillaDeclaracion.url"
              class="w-full h-full"
            ></iframe>
            <p v-else class="p-4 text-xs text-amber-400">
              No se encontró la declaración jurada en PDF. Contacte al administrador.
            </p>
          </div>

          <!-- Lado derecho -->
          <div class="w-full md:w-80 p-4 flex flex-col gap-3">
            <p class="text-xs text-slate-300">
              Después de revisar el documento PDF, haz clic en el siguiente botón para registrar
              tu aceptación. Esta acción quedará registrada como tu declaración jurada de haber
              culminado el curso.
            </p>

            <button
              @click="firmarDeclaracion"
              :disabled="firmando"
              class="inline-flex items-center justify-center rounded-xl bg-emerald-500 hover:bg-emerald-400
                     disabled:bg-emerald-500/60 text-slate-950 text-xs font-semibold px-4 py-2
                     shadow-lg shadow-emerald-500/30"
            >
              <span v-if="!firmando">Firmar declaración</span>
              <span v-else>Firmando...</span>
            </button>

            <p v-if="mensajeFirma" :class="['text-xs', claseMensajeFirma]">
              {{ mensajeFirma }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { useCursoStore } from '../stores/curso'

const curso = useCursoStore()

// Modal
const showDeclaracionModal = ref(false)
const firmando = ref(false)
const mensajeFirma = ref('')
const tipoMensajeFirma = ref('') // 'ok' | 'error'

// Progreso
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

// Abrir / cerrar modal
const abrirModalDeclaracion = () => {
  mensajeFirma.value = ''
  tipoMensajeFirma.value = ''
  showDeclaracionModal.value = true
}

const cerrarModalDeclaracion = () => {
  showDeclaracionModal.value = false
}

// Firmar declaración
const firmarDeclaracion = async () => {
  firmando.value = true
  mensajeFirma.value = ''
  tipoMensajeFirma.value = ''

  try {
    const texto =
      'Declaro bajo juramento que he leído y acepto la declaración jurada del curso de inducción.'
    const resp = await curso.firmarDeclaracion(texto)

    mensajeFirma.value = resp.message || 'Declaración firmada correctamente.'
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
  curso.fetchPlantillaDeclaracion()
})
</script>
