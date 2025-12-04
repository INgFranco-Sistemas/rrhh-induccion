<template>
  <transition name="modal-fade">
    <div
      v-if="show"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm"
      @click.self="handleClose"
    >
      <div
        class="w-full max-w-lg bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl p-6 mx-4"
      >
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-slate-50">
            Registrar nuevo video
          </h2>
          <button
            class="text-slate-400 hover:text-slate-200 text-xl leading-none"
            @click="handleClose"
            :disabled="loadingCreate"
          >
            ×
          </button>
        </div>

        <p class="text-xs text-slate-400 mb-4">
          Completa la información y selecciona un archivo <strong>MP4</strong>.
        </p>

        <form class="space-y-4" @submit.prevent="handleCreate">
          <div>
            <label class="block text-xs font-medium text-slate-200 mb-1">
              Título
            </label>
            <input
              v-model="form.titulo"
              type="text"
              required
              class="w-full rounded-xl bg-slate-950/70 border border-slate-700 text-sm px-3 py-2 text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-sky-500/50 focus:border-sky-500"
              placeholder="Ej: Inducción general a la institución"
            />
          </div>

          <div>
            <label class="block text-xs font-medium text-slate-200 mb-1">
              Descripción
            </label>
            <textarea
              v-model="form.descripcion"
              rows="2"
              class="w-full rounded-xl bg-slate-950/70 border border-slate-700 text-sm px-3 py-2 text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-sky-500/50 focus:border-sky-500 resize-none"
              placeholder="Breve descripción del contenido del video"
            ></textarea>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-slate-200 mb-1">
                Orden
              </label>
              <input
                v-model.number="form.orden"
                type="number"
                min="1"
                required
                class="w-full rounded-xl bg-slate-950/70 border border-slate-700 text-sm px-3 py-2 text-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-500/50 focus:border-sky-500"
              />
            </div>

            <div>
              <label class="block text-xs font-medium text-slate-200 mb-1">
                Duración (seg)
              </label>
              <input
                v-model.number="form.duracion_segundos"
                type="number"
                min="1"
                class="w-full rounded-xl bg-slate-950/70 border border-slate-700 text-sm px-3 py-2 text-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-500/50 focus:border-sky-500"
                placeholder="Opcional"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-medium text-slate-200 mb-1">
              Archivo de video (MP4)
            </label>
            <div
              class="flex items-center justify-between gap-2 rounded-xl border border-dashed border-slate-700 bg-slate-950/70 px-3 py-2"
            >
              <input
                ref="fileInput"
                type="file"
                accept="video/mp4"
                class="w-full text-xs text-slate-300 file:mr-3 file:rounded-lg file:border-0 file:bg-sky-500/80 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-slate-950 hover:file:bg-sky-400"
                @change="handleFileChange"
              />
            </div>
            <p class="mt-1 text-[11px] text-slate-500">
              Tamaño máximo ~200MB (según configuración del backend).
            </p>
          </div>

          <div class="flex items-center justify-between pt-2 gap-3">
            <button
              type="button"
              class="inline-flex items-center justify-center rounded-xl border border-slate-700 bg-slate-800/80 text-slate-200 text-sm font-medium px-4 py-2 hover:bg-slate-700"
              @click="handleClose"
              :disabled="loadingCreate"
            >
              Cancelar
            </button>

            <button
              type="submit"
              :disabled="loadingCreate"
              class="inline-flex items-center justify-center rounded-xl bg-sky-500 hover:bg-sky-400 disabled:bg-sky-500/60 text-slate-950 text-sm font-semibold px-5 py-2.5 shadow-lg shadow-sky-500/30"
            >
              <span v-if="!loadingCreate">Guardar video</span>
              <span v-else class="flex items-center gap-2">
                <span
                  class="h-4 w-4 border-2 border-slate-900 border-t-transparent rounded-full animate-spin"
                ></span>
                Subiendo...
              </span>
            </button>
          </div>

          <p v-if="errorForm" class="text-xs text-red-400">
            {{ errorForm }}
          </p>
          <p v-if="successForm" class="text-xs text-emerald-400">
            {{ successForm }}
          </p>
        </form>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, watch } from 'vue'
import api from '../api/axios'

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  // para calcular próximo orden desde el padre (opcional)
  nextOrder: {
    type: Number,
    default: 1,
  },
})

const emit = defineEmits(['close', 'saved'])

const form = ref({
  titulo: '',
  descripcion: '',
  orden: 1,
  duracion_segundos: null,
  file: null,
})

const fileInput = ref(null)
const loadingCreate = ref(false)
const errorForm = ref(null)
const successForm = ref(null)

// cuando se abre el modal, inicializamos el form
watch(
  () => props.show,
  (value) => {
    if (value) {
      errorForm.value = null
      successForm.value = null
      form.value.titulo = ''
      form.value.descripcion = ''
      form.value.orden = props.nextOrder || 1
      form.value.duracion_segundos = null
      form.value.file = null
      if (fileInput.value) fileInput.value.value = null
    }
  },
)

const handleFileChange = (event) => {
  const file = event.target.files[0]
  form.value.file = file || null
}

const handleClose = () => {
  if (loadingCreate.value) return
  emit('close')
}

const handleCreate = async () => {
  errorForm.value = null
  successForm.value = null

  if (!form.value.file) {
    errorForm.value = 'Debes seleccionar un archivo MP4.'
    return
  }

  loadingCreate.value = true

  try {
    const fd = new FormData()
    fd.append('titulo', form.value.titulo)
    fd.append('descripcion', form.value.descripcion)
    fd.append('orden', form.value.orden)
    if (form.value.duracion_segundos) {
      fd.append('duracion_segundos', form.value.duracion_segundos)
    }
    fd.append('video', form.value.file)

    await api.post('/admvideos', fd, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })

    successForm.value = 'Video registrado correctamente.'
    emit('saved')        // avisamos al padre para que recargue la tabla
    emit('close')        // y cerramos el modal
  } catch (error) {
    console.error(error)
    if (error.response?.status === 422) {
      const data = error.response.data
      if (typeof data === 'object') {
        errorForm.value = Object.values(data)[0]
      } else {
        errorForm.value = 'Error de validación en los datos enviados.'
      }
    } else {
      errorForm.value = 'No se pudo registrar el video.'
    }
  } finally {
    loadingCreate.value = false
  }
}
</script>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.15s ease-out;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}
.modal-fade-enter-active > div,
.modal-fade-leave-active > div {
  transition: transform 0.15s ease-out;
}
.modal-fade-enter-from > div {
  transform: translateY(10px) scale(0.98);
}
.modal-fade-leave-to > div {
  transform: translateY(10px) scale(0.98);
}
</style>
