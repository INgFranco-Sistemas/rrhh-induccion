<template>
  <div class="min-h-[calc(100vh-56px)] bg-slate-950 justify-center
           bg-gradient-to-br from-[#022c22] via-[#02131a] to-[#16a34a]">
    <div class="max-w-4xl mx-auto px-4 py-8">
      <div class="flex items-center justify-between mb-6 gap-3">
        <div>
          <h1 class="text-2xl font-semibold text-slate-50">
            Videos del curso de inducción
          </h1>
          <p class="text-sm text-slate-400">
            Debes ver los videos en el orden establecido.
          </p>
        </div>
        <RouterLink
          :to="{ name: 'trabajador.dashboard' }"
          class="text-xs text-slate-400 hover:text-slate-200"
        >
          ← Volver
        </RouterLink>
      </div>

      <div
        class="bg-slate-900/80 border border-slate-800 rounded-2xl shadow-xl divide-y divide-slate-800"
      >
        <div class="px-4 py-3 text-xs text-slate-400 flex justify-between">
          <span>Lista de videos</span>
          <button
            class="text-xs text-sky-400 hover:text-sky-300"
            @click="curso.fetchEstado"
            :disabled="curso.loading"
          >
            {{ curso.loading ? 'Actualizando...' : 'Actualizar' }}
          </button>
        </div>

        <div v-if="curso.loading" class="px-4 py-4 text-sm text-slate-400">
          Cargando videos...
        </div>

        <div v-else-if="videosConEstado.length === 0" class="px-4 py-4 text-sm text-slate-400">
          Aún no hay videos registrados.
        </div>

        <div
          v-else
          v-for="video in videosConEstado"
          :key="video.id"
          class="px-4 py-3 hover:bg-slate-900/70 transition flex items-center gap-3"
        >
          <div
            class="h-8 w-8 flex items-center justify-center rounded-full"
            :class="video.completado ? 'bg-emerald-600 text-white' : 'bg-slate-800 text-slate-100'"
          >
            {{ video.orden }}
          </div>

          <div class="flex-1">
            <p class="text-sm font-medium text-slate-50">
              {{ video.titulo }}
            </p>
            <p class="text-xs text-slate-400 line-clamp-2">
              {{ video.descripcion || 'Sin descripción' }}
            </p>
          </div>

          <div class="text-xs text-slate-400 w-28">
            <span v-if="video.duracion_segundos">
              {{ video.duracion_segundos }} seg
            </span>
            <span v-else class="text-slate-500">-</span>
            <p v-if="video.completado" class="text-emerald-400 mt-0.5">
              Completado
            </p>
            <p v-else-if="video.bloqueado" class="text-amber-400 mt-0.5">
              Bloqueado
            </p>
          </div>

          <RouterLink
            v-if="!video.bloqueado"
            :to="{ name: 'trabajador.video', params: { id: video.id } }"
            class="inline-flex items-center rounded-lg bg-sky-500 hover:bg-sky-400 text-slate-950 text-[11px] font-semibold px-3 py-1.5"
          >
            Ver
          </RouterLink>

          <button
            v-else
            disabled
            class="inline-flex items-center rounded-lg bg-slate-800 text-slate-500 text-[11px] font-semibold px-3 py-1.5 cursor-not-allowed"
          >
            Ver
          </button>
        </div>
      </div>

      <p v-if="curso.error" class="mt-3 text-xs text-red-400">
        {{ curso.error }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useCursoStore } from '../stores/curso'

const curso = useCursoStore()

const videosConEstado = computed(() => {
  const ordenados = [...curso.videos].sort((a, b) => a.orden - b.orden)
  const primerPend = curso.primerOrdenPendiente

  return ordenados.map((v) => ({
    ...v,
    // Bloquea videos con orden > primer incompleto
    bloqueado: primerPend !== null && v.orden > primerPend,
  }))
})

onMounted(() => {
  curso.fetchEstado()
})
</script>
