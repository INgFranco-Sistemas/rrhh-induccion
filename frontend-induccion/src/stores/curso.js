// src/stores/curso.js
import { defineStore } from 'pinia'
import api from '../api/axios'

export const useCursoStore = defineStore('curso', {
  state: () => ({
    videos: [],
    loading: false,
    error: null,
    puedeFirmar: false,
    declaracionFirmada: false,
    declaracion: null,
  }),

  getters: {
    totalVideos: (state) => state.videos.length,
    completados: (state) => state.videos.filter((v) => v.completado).length,

    // primer video que falta completar
    primerOrdenPendiente: (state) => {
      const pendientes = state.videos.filter((v) => !v.completado)
      if (pendientes.length === 0) return null
      return Math.min(...pendientes.map((v) => v.orden))
    },
  },

  actions: {
    // 👇 AQUÍ es donde usamos /curso/estado
    async fetchEstado() {
      this.loading = true
      this.error = null

      try {
        const { data } = await api.get('/curso/estado')
        console.log('estado curso =>', data)

        // data viene del controlador ProgresoController@estadoCurso
        // estructura: { videos: [...], puede_firmar, declaracion_firmada, declaracion }
        const videos = Array.isArray(data) ? data : (data.videos || [])

        this.videos = videos.map((v) => ({
          ...v,
          completado: !!v.completado,
        }))

        this.puedeFirmar = !!(data.puede_firmar ?? false)
        this.declaracionFirmada = !!(data.declaracion_firmada ?? false)
        this.declaracion = data.declaracion || null
      } catch (error) {
        console.error(error)
        this.error = 'No se pudo cargar el estado del curso.'
      } finally {
        this.loading = false
      }
    },

    // marcar un video como completo en memoria (además de lo que guarda el backend)
    marcarVideoCompletoLocal(videoId) {
      const idNum = Number(videoId)
      const v = this.videos.find((vid) => vid.id === idNum)
      if (v) v.completado = true

      if (this.videos.length > 0) {
        this.puedeFirmar = this.videos.every((vid) => vid.completado === true)
      }
    },

    async firmarDeclaracion(payload = { acepta: true }) {
      // cuando implementes /declaracion/firmar lo conectamos aquí
      const { data } = await api.post('/declaracion/firmar', payload)
      await this.fetchEstado()
      return data
    },
  },
})
