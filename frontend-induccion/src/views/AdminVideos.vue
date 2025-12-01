<template>
  <div class="min-h-[calc(100vh-56px)] bg-slate-950 justify-center
           bg-gradient-to-br from-[#022c22] via-[#02131a] to-[#16a34a]">
    <div class="max-w-6xl mx-auto px-4 py-8">
      <!-- Encabezado -->
      <div class="flex items-center justify-between mb-6 gap-3">
        <div>
          <h1 class="text-2xl font-semibold text-slate-50">
            Gestión de Videos del Curso
          </h1>
          <p class="text-sm text-slate-400">
            Administra los videos MP4 del curso de inducción.
          </p>
        </div>
        <div class="flex items-center gap-3">
          <button
            class="inline-flex items-center rounded-xl bg-sky-500 hover:bg-sky-400 text-slate-950 text-xs font-semibold px-4 py-2 shadow-lg shadow-sky-500/30"
            @click="showModal = true"
          >
            + Nuevo video
          </button>
          <router-link
            :to="{ name: 'admin.dashboard' }"
            class="text-xs text-slate-400 hover:text-slate-200"
          >
            ← Volver
          </router-link>
        </div>
      </div>

      <!-- Tabla de videos -->
      <div class="bg-slate-900/80 border border-slate-800 rounded-2xl shadow-xl">
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-800">
          <h2 class="text-sm font-semibold text-slate-200">
            Lista de videos
          </h2>
          <button
            class="text-xs text-sky-400 hover:text-sky-300"
            @click="fetchVideos"
            :disabled="loadingList"
          >
            {{ loadingList ? 'Actualizando...' : 'Actualizar' }}
          </button>
        </div>

        <div v-if="loadingList" class="p-4 text-sm text-slate-400">
          Cargando videos...
        </div>

        <div v-else-if="videos.length === 0" class="p-4 text-sm text-slate-400">
          No hay videos registrados aún.
        </div>

        <div v-else class="divide-y divide-slate-800">
          <div
            class="hidden md:grid grid-cols-[60px_1.5fr_1fr_100px_80px] gap-3 px-4 py-2 text-[11px] font-semibold uppercase tracking-wide text-slate-400 bg-slate-900/90"
          >
            <span>#</span>
            <span>Título</span>
            <span>Descripción</span>
            <span>Duración</span>
            <span class="text-center">Acciones</span>
          </div>

          <div
            v-for="video in videos"
            :key="video.id"
            class="grid grid-cols-1 md:grid-cols-[60px_1.5fr_1fr_100px_80px] gap-3 px-4 py-3 hover:bg-slate-900/70 transition"
          >
            <div class="flex items-center text-xs text-slate-400">
              <span
                class="inline-flex items-center justify-center h-7 w-7 rounded-full bg-slate-800 text-slate-200 text-xs font-semibold"
              >
                {{ video.orden }}
              </span>
            </div>

            <div>
              <p class="text-sm font-medium text-slate-50">
                {{ video.titulo }}
              </p>
              <p class="text-[11px] text-slate-500">
                ID: {{ video.id }} · Archivo: {{ video.file_path }}
              </p>
            </div>

            <div class="text-xs text-slate-400">
              <p class="line-clamp-2">
                {{ video.descripcion || 'Sin descripción' }}
              </p>
            </div>

            <div class="text-xs text-slate-300">
              <span v-if="video.duracion_segundos">
                {{ video.duracion_segundos }} seg
              </span>
              <span v-else class="text-slate-500">
                No registrado
              </span>
            </div>

            <div class="flex items-center justify-start md:justify-center gap-2">
              <button
                class="inline-flex items-center justify-center rounded-lg bg-red-500/80 hover:bg-red-500 text-[11px] font-semibold text-white px-3 py-1.5"
                @click="handleDelete(video)"
                :disabled="loadingDeleteId === video.id"
              >
                <span v-if="loadingDeleteId !== video.id">Eliminar</span>
                <span v-else>Eliminando...</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <p v-if="errorList" class="mt-3 text-xs text-red-400">
        {{ errorList }}
      </p>
    </div>

    <!-- Modal componente -->
    <VideoFormModal
      :show="showModal"
      :next-order="nextOrder"
      @close="showModal = false"
      @saved="handleSaved"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../api/axios'
import VideoFormModal from '../components/VideoFormModal.vue'

const videos = ref([])
const loadingList = ref(false)
const errorList = ref(null)
const loadingDeleteId = ref(null)
const showModal = ref(false)

const nextOrder = computed(() => (videos.value.length || 0) + 1)

const fetchVideos = async () => {
  loadingList.value = true
  errorList.value = null
  try {
    const { data } = await api.get('/videos')
    videos.value = data
  } catch (error) {
    console.error(error)
    errorList.value = 'No se pudo cargar la lista de videos.'
  } finally {
    loadingList.value = false
  }
}

onMounted(fetchVideos)

const handleDelete = async (video) => {
  if (
    !confirm(
      `¿Seguro que deseas eliminar el video "${video.titulo}" (orden ${video.orden})?`,
    )
  ) {
    return
  }

  loadingDeleteId.value = video.id
  errorList.value = null

  try {
    await api.delete(`/videos/${video.id}`)
    videos.value = videos.value.filter((v) => v.id !== video.id)
  } catch (error) {
    console.error(error)
    errorList.value = 'No se pudo eliminar el video.'
  } finally {
    loadingDeleteId.value = null
  }
}

const handleSaved = async () => {
  // se llama cuando el modal termina de crear el video
  await fetchVideos()
}
</script>
