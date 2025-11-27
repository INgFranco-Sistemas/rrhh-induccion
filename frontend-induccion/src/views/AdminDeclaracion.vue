<template>
  <div class="min-h-[calc(100vh-56px)] bg-slate-950">
    <div class="max-w-4xl mx-auto px-4 py-8">
      <div class="flex items-center justify-between mb-6 gap-3">
        <div>
          <h1 class="text-2xl font-semibold text-slate-50">
            Declaración jurada del curso
          </h1>
          <p class="text-sm text-slate-400">
            Sube el archivo PDF que los trabajadores deberán revisar y aceptar
            al culminar el curso de inducción.
          </p>
        </div>
        <RouterLink
          :to="{ name: 'admin.dashboard' }"
          class="text-xs text-slate-400 hover:text-slate-200"
        >
          ← Volver al panel
        </RouterLink>
      </div>

      <!-- Estado actual -->
      <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 shadow-lg mb-6">
        <p class="text-sm font-semibold text-slate-50 mb-2">
          Plantilla actual
        </p>

        <div v-if="loading" class="text-sm text-slate-400">
          Cargando información...
        </div>

        <div v-else-if="!template" class="text-sm text-slate-400">
          Aún no se ha configurado una declaración jurada.
        </div>

        <div v-else class="space-y-1 text-sm text-slate-300">
          <p>
            <span class="font-semibold">Archivo:</span>
            {{ template.nombre }}
          </p>
          <p class="text-xs text-slate-400">
            Actualizado el
            {{ new Date(template.updated_at).toLocaleString() }}
          </p>
          <a
            v-if="template.url"
            :href="template.url"
            target="_blank"
            rel="noopener"
            class="inline-flex items-center mt-2 text-xs text-sky-400 hover:text-sky-300"
          >
            Ver PDF actual →
          </a>
        </div>
      </div>

      <!-- Formulario de carga -->
      <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 shadow-lg">
        <p class="text-sm font-semibold text-slate-50 mb-3">
          Subir nueva declaración jurada (PDF)
        </p>

        <form class="space-y-4" @submit.prevent="handleUpload">
          <div>
            <label class="block text-xs font-medium text-slate-200 mb-1">
              Archivo PDF
            </label>
            <input
              ref="fileInput"
              type="file"
              accept="application/pdf"
              class="w-full text-xs text-slate-300 file:mr-3 file:rounded-lg file:border-0 file:bg-sky-500/80 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-slate-950 hover:file:bg-sky-400"
              @change="handleFileChange"
            />
            <p class="mt-1 text-[11px] text-slate-500">
              Solo se acepta formato PDF. Tamaño máximo aproximado: 20MB.
            </p>
          </div>

          <div class="flex items-center justify-end gap-3">
            <button
              type="submit"
              :disabled="subiendo || !file"
              class="inline-flex items-center rounded-xl bg-sky-500 hover:bg-sky-400 disabled:bg-sky-500/60 text-slate-950 text-xs font-semibold px-5 py-2.5 shadow-lg shadow-sky-500/30"
            >
              <span v-if="!subiendo">Guardar declaración</span>
              <span v-else>Subiendo...</span>
            </button>
          </div>

          <p v-if="error" class="text-xs text-red-400">
            {{ error }}
          </p>
          <p v-if="mensaje" class="text-xs text-emerald-400">
            {{ mensaje }}
          </p>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import api from '../api/axios'

const template = ref(null)
const loading = ref(false)
const error = ref('')
const mensaje = ref('')
const subiendo = ref(false)
const file = ref(null)
const fileInput = ref(null)

const cargarTemplate = async () => {
  loading.value = true
  error.value = ''
  try {
    const { data } = await api.get('/admin/declaracion-plantilla')
    template.value = data
  } catch (err) {
    console.error(err)
    error.value = 'No se pudo cargar la información de la declaración.'
  } finally {
    loading.value = false
  }
}

const handleFileChange = (e) => {
  const f = e.target.files[0]
  file.value = f || null
  mensaje.value = ''
  error.value = ''
}

const handleUpload = async () => {
  if (!file.value) {
    error.value = 'Debes seleccionar un archivo PDF.'
    return
  }

  subiendo.value = true
  error.value = ''
  mensaje.value = ''

  try {
    const fd = new FormData()
    fd.append('archivo', file.value)

    const { data } = await api.post('/admin/declaracion-plantilla', fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    mensaje.value = data.message || 'Declaración jurada actualizada correctamente.'
    template.value = data.template

    // Limpiar input
    file.value = null
    if (fileInput.value) fileInput.value.value = null
  } catch (err) {
    console.error(err)
    if (err.response?.data?.errors?.archivo) {
      error.value = err.response.data.errors.archivo[0]
    } else {
      error.value =
        err.response?.data?.message ||
        'No se pudo subir el archivo. Verifica que sea un PDF válido.'
    }
  } finally {
    subiendo.value = false
  }
}

onMounted(() => {
  cargarTemplate()
})
</script>
