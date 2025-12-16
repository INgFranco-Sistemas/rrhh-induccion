import { defineStore } from 'pinia'
import api from '../api/axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: null,
    isInitialized: false,
    loading: false,
    error: null,
    sede: null,
    roles: [],
    rol_principal: null,
    permissions: [],
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    // isAdmin: (state) => state.user?.role === 'admin',
    // isTrabajador: (state) => state.user?.role === 'trabajador',
  },

  actions: {
    initFromLocalStorage() {
      const token = localStorage.getItem('token')
      const user = localStorage.getItem('user')

      if (token) {
        this.token = token
      }
      if (user) {
        this.user = JSON.parse(user)
      }

      this.isInitialized = true
    },

    async login(adm_email, password) {
      this.loading = true
      this.error = null

      try {
        const { data } = await api.post('/auth/login', { adm_email, password })

        this.token = data.access_token
        this.user = data.user
        this.sede = data.sede

        // 🔒 Filtrar roles: solo los permitidos
        this.roles = data.roles || []

        this.rol_principal = data.rol_principal

        this.permissions = data.permissions || []

        localStorage.setItem('token', this.token)
        localStorage.setItem('user', JSON.stringify(this.user))
      } catch (error) {
        this.error = error.response?.data?.message || 'Error al iniciar sesión'
        throw error
      } finally {
        this.loading = false
      }
    },

     async refresh() {
      try {
        const { data } = await api.post('/auth/refresh', {}, {
          headers: {
            Authorization: `Bearer ${this.token}`
          }
        })

        this.token = data.access_token
        this.expires_in = data.expires_in
        this.user = data.user
        this.sede = data.sede
        this.roles = data.roles || []
        this.rol_principal = data.rol_principal
        this.permissions = data.permissions || []

        localStorage.setItem('token', this.token)
        localStorage.setItem('user', JSON.stringify(this.user))

        return data
      } catch (error) {
        console.error('[AuthStore] Error al refrescar token:', error)
        this.logout()
        throw error
      }
    },

    logout() {
      this.token = null
      this.user = null
      this.roles = []
      localStorage.removeItem('token')
      localStorage.removeItem('user')
    },
  },

  persist: true
})
