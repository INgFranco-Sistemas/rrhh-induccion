// import axios from 'axios'
// import { useAuthStore } from '../stores/auth'

// const api = axios.create({
//   baseURL: import.meta.env.VITE_API_BASE_URL, // <--- Usa la variable de entorno de Vite aquí
// })

// api.interceptors.request.use((config) => {
//   const auth = useAuthStore()
//   if (auth.token) {
//     config.headers.Authorization = `Bearer ${auth.token}`
//   }
//   return config
// })

// export default api

import axios from 'axios'
import { useAuthStore } from '../stores/auth'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
})

// Interceptor de request: añade el token
api.interceptors.request.use((config) => {
  const auth = useAuthStore()
  if (auth.token) {
    config.headers.Authorization = `Bearer ${auth.token}`
  }
  return config
})

// Interceptor de respuesta: maneja expiración y refresh
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const auth = useAuthStore()
    const originalRequest = error.config

    // Si el error es 401 y no hemos intentado refresh aún
    if (error.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true
      try {
        // Llamar al endpoint de refresh
        const { data } = await axios.post(
          `${import.meta.env.VITE_API_BASE_URL}/auth/refresh`,
          {},
          {
            headers: {
              Authorization: `Bearer ${auth.token}`,
            },
          }
        )

        // Guardar nuevo token en store y localStorage
        auth.token = data.access_token
        localStorage.setItem('token', auth.token)

        // Reintentar la petición original con el nuevo token
        originalRequest.headers.Authorization = `Bearer ${auth.token}`
        return api(originalRequest)
      } catch (refreshError) {
        console.error('[Axios] Error al refrescar token:', refreshError)
        auth.logout()
        return Promise.reject(refreshError)
      }
    }

    return Promise.reject(error)
  }
)

api.interceptors.response.use(
  (response) => response,
  (error) => {
    const auth = useAuthStore()
    if (error.response?.status === 401) {
      auth.logout()
      window.location.href = '/login' // 🔒 redirige al login
    }
    return Promise.reject(error)
  }
)

export default api
