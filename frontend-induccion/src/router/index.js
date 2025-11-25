import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import LoginView from '../views/LoginView.vue'

// Más adelante agregaremos otras vistas
const routes = [
  {
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: { public: true },
  },
  {
    path: '/',
    name: 'home',
    component: () => import('../views/DashboardView.vue'),
    meta: { requiresAuth: true },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const auth = useAuthStore()

  if (!auth.isInitialized) {
    auth.initFromLocalStorage()
  }

  if (to.meta.public) {
    if (auth.isAuthenticated && to.name === 'login') {
      return next({ name: 'home' })
    }
    return next()
  }

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return next({ name: 'login' })
  }

  next()
})

export default router
