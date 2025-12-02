<template>
  <div class="min-h-[calc(100vh-56px)] bg-gradient-to-br from-[#022c22] via-[#02131a] to-[#16a34a]">
    <div class="max-w-5xl mx-auto px-4 py-8 space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between gap-3">
        <div>
          <p class="text-xs text-slate-400 mb-1">
            Seguimiento de Personal · Detalle de trabajador
          </p>
          <h1 class="text-2xl font-semibold text-slate-50">
            {{ usuario?.nombre || 'Trabajador' }}
          </h1>
          <p class="text-sm text-slate-400">
            {{ usuario?.correo }}
          </p>
        </div>

        <div class="flex flex-col items-end gap-2">
          <RouterLink
            :to="{ name: 'admin.seguimiento' }"
            class="text-xs text-slate-400 hover:text-slate-200"
          >
            ← Volver al seguimiento
          </RouterLink>

          <button
            class="text-xs text-sky-400 hover:text-sky-300"
            @click="fetchDetalle"
            :disabled="loading"
          >
            {{ loading ? 'Actualizando...' : 'Actualizar' }}
          </button>
        </div>
      </div>

      <!-- Resumen -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4">
          <p class="text-[11px] uppercase tracking-wide text-slate-400">Videos completados</p>
          <p class="mt-2 text-2xl font-semibold text-slate-50">
            {{ resumen.videos_completados || 0 }}/{{ resumen.total_videos || 0 }}
          </p>
        </div>

        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4">
          <p class="text-[11px] uppercase tracking-wide text-slate-400">% Avance</p>
          <p class="mt-2 text-2xl font-semibold text-sky-300">
            {{ resumen.porcentaje || 0 }}%
          </p>
        </div>

        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4">
          <p class="text-[11px] uppercase tracking-wide text-slate-400">Estado</p>
          <p class="mt-3">
            <span
              class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold"
              :class="{
                'bg-amber-500/10 text-amber-300 border border-amber-500/40': resumen.estado === 'no_iniciado',
                'bg-sky-500/10 text-sky-300 border border-sky-500/40': resumen.estado === 'en_progreso',
                'bg-emerald-500/10 text-emerald-300 border border-emerald-500/40': resumen.estado === 'completado',
              }"
            >
              {{
                resumen.estado === 'no_iniciado'
                  ? 'No iniciado'
                  : resumen.estado === 'en_progreso'
                    ? 'En progreso'
                    : 'Completado'
              }}
            </span>
          </p>
        </div>

        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4">
          <p class="text-[11px] uppercase tracking-wide text-slate-400">Declaración</p>
          <p class="mt-3">
            <span
              class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold"
              :class="resumen.declaracion_firmada
                ? 'bg-emerald-500/10 text-emerald-300 border border-emerald-500/40'
                : 'bg-amber-500/10 text-amber-300 border border-amber-500/40'"
            >
              {{ resumen.declaracion_firmada ? 'Firmada' : 'Pendiente' }}
            </span>
          </p>
          <p v-if="resumen.declaracion_fecha" class="mt-1 text-[11px] text-slate-400">
            Firmada el: {{ new Date(resumen.declaracion_fecha).toLocaleString() }}
          </p>
        </div>
      </div>

      <!-- Barra de progreso -->
      <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 space-y-3">
        <p class="text-[11px] uppercase tracking-wide text-slate-400">Progreso del curso</p>
        <div class="w-full h-2.5 bg-slate-800 rounded-full overflow-hidden">
          <div
            class="h-full bg-emerald-500 transition-all duration-500"
            :style="{ width: (resumen.porcentaje || 0) + '%' }"
          ></div>
        </div>
        <p class="text-xs text-slate-400">
          Última actividad:
          <span class="text-slate-200">
            {{
              resumen.ultima_actividad
                ? new Date(resumen.ultima_actividad).toLocaleString()
                : 'Sin registros'
            }}
          </span>
        </p>
      </div>

      <!-- Tabla de videos -->
      <div class="bg-slate-900/80 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="px-4 py-3 text-xs text-slate-400">
          Detalle por video
        </div>

        <div v-if="loading" class="px-4 py-4 text-sm text-slate-400">
          Cargando detalle...
        </div>

        <div v-else-if="videos.length === 0" class="px-4 py-4 text-sm text-slate-400">
          No hay videos registrados en el curso.
        </div>

        <div v-else class="overflow-x-auto">
          <table class="min-w-full text-xs">
            <thead class="bg-slate-900/90">
              <tr class="text-left text-slate-400">
                <th class="px-4 py-2">#</th>
                <th class="px-4 py-2">Título</th>
                <th class="px-4 py-2">Duración</th>
                <th class="px-4 py-2">Visto</th>
                <th class="px-4 py-2 text-center">Estado</th>
                <th class="px-4 py-2 text-center">Completado</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="v in videos"
                :key="v.id"
                class="border-t border-slate-800/70 hover:bg-slate-900/60"
              >
                <td class="px-4 py-2 text-slate-300">
                  {{ v.orden }}
                </td>
                <td class="px-4 py-2 text-slate-50">
                  {{ v.titulo }}
                </td>
                <td class="px-4 py-2 text-slate-300">
                  {{ v.duracion_segundos ? v.duracion_segundos + ' seg' : '-' }}
                </td>
                <td class="px-4 py-2 text-slate-300">
                  {{ v.segundos_vistos ? v.segundos_vistos + ' seg' : 'Sin registro' }}
                </td>
                <td class="px-4 py-2 text-center">
                  <span
                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold"
                    :class="v.completado
                      ? 'bg-emerald-500/10 text-emerald-300 border border-emerald-500/40'
                      : 'bg-slate-700/50 text-slate-300 border border-slate-600/60'"
                  >
                    {{ v.completado ? 'Completado' : 'Pendiente' }}
                  </span>
                </td>
                <td class="px-4 py-2 text-center text-slate-400">
                  {{
                    v.completado_at
                      ? new Date(v.completado_at).toLocaleString()
                      : '—'
                  }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <p v-if="error" class="px-4 py-3 text-xs text-red-400">
          {{ error }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import api from '../api/axios'

const route = useRoute()

const loading = ref(false)
const error = ref(null)
const usuario = ref(null)
const resumen = ref({})
const videos = ref([])

const fetchDetalle = async () => {
  loading.value = true
  error.value = null
  try {
    const { data } = await api.get(`/admin/seguimiento/usuario/${route.params.id}`)
    usuario.value = data.usuario
    resumen.value = data.resumen || {}
    videos.value = data.videos || []
  } catch (err) {
    console.error(err)
    error.value = 'No se pudo cargar el detalle del trabajador.'
  } finally {
    loading.value = false
  }
}

onMounted(fetchDetalle)
</script>
