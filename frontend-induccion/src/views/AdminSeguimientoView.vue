<template>
  <div class="min-h-[calc(100vh-56px)] bg-gradient-to-br from-[#022c22] via-[#02131a] to-[#16a34a]">
    <div class="max-w-6xl mx-auto px-4 py-8 space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between gap-3">
        <div>
          <h1 class="text-2xl font-semibold text-slate-50">
            Seguimiento de Personal
          </h1>
          <p class="text-sm text-slate-400">
            Revisa el avance de los trabajadores en el curso de inducción.
          </p>
        </div>

        <RouterLink
          :to="{ name: 'admin.dashboard' }"
          class="text-xs text-slate-400 hover:text-slate-200"
        >
          ← Volver al panel
        </RouterLink>
      </div>

      <!-- Resumen -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4">
          <p class="text-[11px] uppercase tracking-wide text-slate-400">Trabajadores</p>
          <p class="mt-2 text-2xl font-semibold text-slate-50">
            {{ resumen.total_trabajadores ?? 0 }}
          </p>
        </div>

        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4">
          <p class="text-[11px] uppercase tracking-wide text-slate-400">No iniciado</p>
          <p class="mt-2 text-2xl font-semibold text-amber-300">
            {{ resumen.no_iniciado ?? 0 }}
          </p>
        </div>

        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4">
          <p class="text-[11px] uppercase tracking-wide text-slate-400">En progreso</p>
          <p class="mt-2 text-2xl font-semibold text-sky-300">
            {{ resumen.en_progreso ?? 0 }}
          </p>
        </div>

        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4">
          <p class="text-[11px] uppercase tracking-wide text-slate-400">Completado</p>
          <p class="mt-2 text-2xl font-semibold text-emerald-400">
            {{ resumen.completado ?? 0 }}
          </p>
        </div>
      </div>

      <!-- Tabla -->
      <div class="bg-slate-900/80 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="px-4 py-3 flex items-center justify-between text-xs text-slate-400">
          <span>Detalle por trabajador</span>
          <button
            class="text-xs text-sky-400 hover:text-sky-300"
            @click="fetchSeguimiento"
            :disabled="loading"
          >
            {{ loading ? 'Actualizando...' : 'Actualizar' }}
          </button>
        </div>

        <div v-if="loading" class="px-4 py-4 text-sm text-slate-400">
          Cargando información...
        </div>

        <div v-else-if="trabajadores.length === 0" class="px-4 py-4 text-sm text-slate-400">
          No hay registros de trabajadores.
        </div>

        <div v-else class="overflow-x-auto">
          <table class="min-w-full text-xs">
            <thead class="bg-slate-900/90">
              <tr class="text-left text-slate-400">
                <th class="px-4 py-2">Trabajador</th>
                <th class="px-4 py-2">Correo</th>
                <th class="px-4 py-2 text-center">Videos</th>
                <th class="px-4 py-2 text-center">% Avance</th>
                <th class="px-4 py-2 text-center">Estado</th>
                <th class="px-4 py-2 text-center">Última actividad</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="t in trabajadores"
                :key="t.id"
                class="border-t border-slate-800/70 hover:bg-slate-900/60"
              >
                <td class="px-4 py-2 text-slate-50">
                  {{ t.nombre }}
                </td>
                <td class="px-4 py-2 text-slate-400">
                  {{ t.correo }}
                </td>
                <td class="px-4 py-2 text-center text-slate-200">
                  {{ t.videos_completados }}/{{ t.total_videos }}
                </td>
                <td class="px-4 py-2 text-center text-slate-200">
                  {{ t.porcentaje }}%
                </td>
                <td class="px-4 py-2 text-center">
                  <span
                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold"
                    :class="{
                      'bg-amber-500/10 text-amber-300 border border-amber-500/40': t.estado === 'no_iniciado',
                      'bg-sky-500/10 text-sky-300 border border-sky-500/40': t.estado === 'en_progreso',
                      'bg-emerald-500/10 text-emerald-300 border border-emerald-500/40': t.estado === 'completado',
                    }"
                  >
                    {{
                      t.estado === 'no_iniciado'
                        ? 'No iniciado'
                        : t.estado === 'en_progreso'
                          ? 'En progreso'
                          : 'Completado'
                    }}
                  </span>
                </td>
                <td class="px-4 py-2 text-center text-slate-400">
                  {{ t.ultima_actividad ? new Date(t.ultima_actividad).toLocaleString() : '—' }}
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
import { RouterLink } from 'vue-router'
import api from '../api/axios'

const loading = ref(false)
const error = ref(null)
const resumen = ref({})
const trabajadores = ref([])

const fetchSeguimiento = async () => {
  loading.value = true
  error.value = null
  try {
    const { data } = await api.get('/admin/seguimiento/curso')
    resumen.value = data.resumen || {}
    trabajadores.value = data.trabajadores || []
  } catch (err) {
    console.error(err)
    error.value = 'No se pudo cargar la información de seguimiento.'
  } finally {
    loading.value = false
  }
}

onMounted(fetchSeguimiento)
</script>
