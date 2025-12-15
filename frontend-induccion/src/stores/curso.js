import { defineStore } from 'pinia'
import api from '../api/axios'
import FirmaPeruIntegrador from '../FirmaPeru'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL.replace('/api', '') // quitamos /api para usar host base

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

    async firmarDeclaracion(texto, idfile = null) {
      // const { data } = await api.post('/declaracion/firmar', {
      //   texto_declaracion: texto,
      //   idfile: idfile,
      // })
      // await this.fetchEstado()
      // return data

      let firmador = new FirmaPeruIntegrador({
        getParams: () => {
          // let route = "http://127.0.0.1:8000/app/tramite/firma_token_params/"
          // if (process.env.MIX_PRODUCCION != 0) {
          //     route = 'http://proyectos.regionhuanuco.gob.pe/app/tramite/firma_token_params/'
          // }
          let parametros = {
            // "param_url": route + r.data.id + "/" + r.data.token,
            // "param_url": "http://firmaperu.test/parametros2.php",
            // "param_url": "http://127.0.0.1:8000/firmaperu/parametros",
            "param_url": API_BASE_URL+ "/firmaperu/parametros?idfile="+ idfile,
            "param_token": "1626476967",
            "document_extension": "pdf"
          }
          console.log(JSON.stringify(parametros))
          return btoa(JSON.stringify(parametros))
        }
      })
      firmador.startSignature()

    },
  },
})
