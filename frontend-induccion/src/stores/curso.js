import { defineStore } from 'pinia'
import api from '../api/axios'
// import FirmaPeruIntegrador from '../FirmaPeru'
import { useAuthStore } from './auth'

// const API_BASE_URL = import.meta.env.VITE_API_BASE_URL.replace('/api', '') // quitamos /api para usar host base
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL // quitamos /api para usar host base

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
        // console.log('plantilla desde API =>', data)
        this.plantillaDeclaracion = data          // 👈 SOLO guarda data
      } catch (error) {
        console.error('Error cargando plantilla de declaración:', error)
        this.plantillaDeclaracion = null
      }
    },

    firmarDeclaracion(texto, idfile = null) {

      const auth = useAuthStore()

      const cargo = auth.user.adm_cargo.split(" ").join("_");

      let parametros = {
        param_url: `${API_BASE_URL}/firmaperu/parametros/${idfile}/${cargo}/${auth.user.id}`,
        param_token: "1626476967",
        document_extension: "pdf"
      }
      console.log(JSON.stringify(parametros))
      const datosb64 = btoa(JSON.stringify(parametros))
      console.log("Datos en base64:", datosb64)
      startSignature(48596, datosb64);

      return { message: 'Proceso de firma iniciado.' }
    },
    
    async firmarfromatoDeclaracion(texto) {
      const auth = useAuthStore()

      try {
        const { data } = await api.post('/declaracion/firmar', {
          texto_declaracion: texto || '',
          iduser: auth.user.id,
        })
        await this.fetchEstado()
        this.declaracionFirmada = true
        console.log('[FirmaPerú] Firma registrada correctamente', data)
      } catch (error) {
        console.error('[FirmaPerú] Error al registrar firma:', error)
      }
    }
  },


  // persist: true
})
