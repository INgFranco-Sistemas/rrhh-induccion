<template>
  <div class="min-h-[calc(100vh-56px)] bg-slate-950">
    <div class="max-w-4xl mx-auto px-4 py-8">
      <div class="flex items-center justify-between mb-4 gap-3">
        <div>
          <p class="text-xs uppercase tracking-wide text-sky-400 mb-1">
            Video {{ video?.orden ?? '' }}
          </p>
          <h1 class="text-xl font-semibold text-slate-50">
            {{ video?.titulo || 'Cargando...' }}
          </h1>
        </div>
        <RouterLink
          :to="{ name: 'trabajador.videos' }"
          class="text-xs text-slate-400 hover:text-slate-200"
        >
          ← Volver a la lista
        </RouterLink>
      </div>

      <p v-if="video" class="text-sm text-slate-400 mb-4">
        {{ video.descripcion }}
      </p>

      <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 shadow-xl">
        <!-- Estado de carga / error -->
        <div v-if="loadingVideo" class="text-sm text-slate-400">
          Cargando video...
        </div>

        <div v-else-if="loadError" class="text-sm text-red-400">
          {{ loadError }}
        </div>

        <!-- Contenido principal -->
        <div v-else-if="video" class="space-y-4">
          <video
            ref="videoRef"
            class="w-full rounded-xl border border-slate-800 bg-black"
            controls
            :src="videoUrl"
            @loadedmetadata="handleLoadedMetadata"
            @ended="handleEnded"
          ></video>

          <div class="flex items-center justify-between text-xs text-slate-400">
            <span>Duración aprox: {{ durationDisplay }}</span>
            <span v-if="sendingProgress">Enviando progreso...</span>
            <span v-else-if="message" :class="messageClass">
              {{ message }}
            </span>
          </div>
        </div>

        <div v-else class="text-sm text-slate-400">
          No se pudo cargar la información del video.
        </div>
      </div>

      <div class="mt-6 bg-slate-900/80 border border-slate-800 rounded-2xl p-4 text-sm text-slate-300">
        <p class="font-semibold mb-1">Importante</p>
        <p>
          Debes reproducir el video completo. El sistema registrará automáticamente la
          finalización y se habilitará el siguiente video según el orden establecido.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import api from '../api/axios'
import { useCursoStore } from '../stores/curso'

const route = useRoute()
const id = route.params.id
const curso = useCursoStore()

const video = ref(null)
const loadingVideo = ref(false)
const loadError = ref('')
const videoRef = ref(null)
const duration = ref(0)

const sendingProgress = ref(false)
const message = ref('')
const messageType = ref('') // 'ok' | 'error'

// ========= Cálculos =========
const durationDisplay = computed(() => {
  if (!duration.value) return '-'
  const mins = Math.floor(duration.value / 60)
  const secs = Math.round(duration.value % 60)
  return `${mins} min ${secs} seg`
})

const messageClass = computed(() => {
  return messageType.value === 'ok'
    ? 'text-emerald-400'
    : messageType.value === 'error'
    ? 'text-red-400'
    : 'text-slate-400'
})

// Construcción de URL del video
const videoUrl = computed(() => {
  if (video.value?.url) return video.value.url
  if (video.value?.file_path) {
    // Ajusta el host si tu backend no está en 127.0.0.1:8000
    return `http://127.0.0.1:8000/storage/${video.value.file_path}`
  }
  return ''
})

// ========= Lógica =========
const fetchVideo = async () => {
  loadingVideo.value = true
  loadError.value = ''
  try {
    console.log('Cargando video con id =>', id)
    const { data } = await api.get(`/videos/${id}`)
    console.log('Video cargado =>', data)
    video.value = data
  } catch (err) {
    console.error('Error cargando video', err)
    loadError.value =
      err.response?.data?.message || 'No se pudo cargar la información del video.'
    video.value = null
  } finally {
    loadingVideo.value = false
  }
}

const handleLoadedMetadata = () => {
  if (videoRef.value) {
    duration.value = videoRef.value.duration || 0
  }
}

const handleEnded = async () => {
  if (!duration.value && videoRef.value) {
    duration.value = videoRef.value.duration || 0
  }

  sendingProgress.value = true
  message.value = ''
  messageType.value = ''

  try {
    const segundos = Math.round(duration.value || 0)
    const { data } = await api.post(`/videos/${id}/progreso`, {
      segundos_vistos: segundos,
      completado: true,
    })

    console.log('Respuesta progreso:', data)
    // Actualizar estado local del curso
    curso.marcarVideoCompletoLocal(id)

    message.value = 'Progreso registrado. Puedes continuar con el siguiente video.'
    messageType.value = 'ok'
  } catch (err) {
    console.error('Error registrando progreso', err)
    message.value =
      err.response?.data?.message ||
      'No se pudo registrar el progreso. Intenta nuevamente.'
    messageType.value = 'error'
  } finally {
    sendingProgress.value = false
  }
}

onMounted(async () => {
  await fetchVideo()
  // Por si el store aún está vacío
  if (!curso.videos.length) {
    await curso.fetchEstado()
  }
})
</script>
