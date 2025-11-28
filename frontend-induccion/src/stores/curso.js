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
    plantillaDeclaracion: null, // PDF que sube el admin
  }),

  getters: {
    totalVideos: (state) => state.videos.length,
    completados: (state) => state.videos.filter((v) => v.completado).length,
  },

  actions: {
    async fetchEstado() {
      this.loading = true
      this.error = null

      try {
        const { data } = await api.get('/curso/estado')
        const videos = Array.isArray(data) ? data : data.videos || []

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

   async fetchPlantillaDeclaracion() {
  try {
    const { data } = await api.get('/declaracion-plantilla') // 👈
    console.log('plantilla desde API =>', data)
    this.plantillaDeclaracion = data          // 👈 SOLO guarda data
  } catch (error) {
    console.error('Error cargando plantilla de declaración:', error)
    this.plantillaDeclaracion = null
  }
},

    async firmarDeclaracion(texto) {
      const { data } = await api.post('/declaracion/firmar', {
        texto_declaracion: texto,
      })
      await this.fetchEstado()
      return data
    },
  },
})
