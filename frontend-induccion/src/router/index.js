import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import LoginView from '../views/LoginView.vue'
import AdminDashboard from '../views/AdminDashboard.vue'
import TrabajadorDashboard from '../views/TrabajadorDashboard.vue'
import AdminVideos from '../views/AdminVideos.vue'
import TrabajadorVideos from '../views/TrabajadorVideos.vue'
import VideoPlayerView from '../views/VideoPlayerView.vue'
import AdminDeclaracion from '../views/AdminDeclaracion.vue'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: { public: true },
  },
  // Redirección raíz según rol
  {
    path: '/',
    name: 'root',
    meta: { requiresAuth: true },
    beforeEnter: (to, from, next) => {
      const auth = useAuthStore()
      if (auth.isAdmin) return next({ name: 'admin.dashboard' })
      return next({ name: 'trabajador.dashboard' })
    },
  },
  {
    path: '/admin',
    name: 'admin.dashboard',
    component: AdminDashboard,
    meta: { requiresAuth: true, role: 'admin' },
  },
  {
    path: '/admin/videos',
    name: 'admin.videos',
    component: AdminVideos,
    meta: { requiresAuth: true, role: 'admin' },
  },
  {
    path: '/curso',
    name: 'trabajador.dashboard',
    component: TrabajadorDashboard,
    meta: { requiresAuth: true },
  },
  {
    path: '/curso/videos',
    name: 'trabajador.videos',
    component: TrabajadorVideos,
    meta: { requiresAuth: true },
  },
  {
    path: '/curso/videos/:id',
    name: 'trabajador.video',
    component: VideoPlayerView,
    meta: { requiresAuth: true },
    props: true,
  },
  {
    path: '/admin/declaracion',
    name: 'admin.declaracion',
    component: AdminDeclaracion,
    meta: { requiresAuth: true }, // y si manejas roles: role: 'admin'
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Guard global
router.beforeEach((to, from, next) => {
  const auth = useAuthStore()

  if (!auth.isInitialized) {
    auth.initFromLocalStorage()
  }

  if (to.meta.public) {
    if (auth.isAuthenticated && to.name === 'login') {
      // Si ya está logueado, lo mando al root y ahí se redirige por rol
      return next({ name: 'root' })
    }
    return next()
  }

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return next({ name: 'login' })
  }

  // Verificar rol si la ruta lo exige
  if (to.meta.role && auth.user?.role !== to.meta.role) {
    return next({ name: 'root' })
  }

  next()
})

export default router
