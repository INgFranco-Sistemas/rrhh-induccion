<template>
  <div class="min-h-[calc(100vh-56px)] bg-slate-950 justify-center
           bg-gradient-to-br from-[#022c22] via-[#02131a] to-[#16a34a]">
    <div class="max-w-6xl mx-auto px-4 py-8">
      <h1 class="text-2xl font-semibold text-slate-50 mb-2">
        Panel del Administrador
      </h1>
      <p class="text-sm text-slate-400 mb-6">
        Gestiona los videos del curso de inducción y revisa el avance de los trabajadores.
      </p>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-slate-900/80 border border-sky-500/30 rounded-2xl p-5 shadow-lg">
          <p class="text-xs uppercase tracking-wide text-sky-400 mb-1">
            Curso
          </p>
          <p class="text-lg font-semibold text-slate-50">
            Inducción General
          </p>
          <p class="mt-2 text-sm text-slate-400">
            Videos, estructura y configuración del curso único.
          </p>
        </div>

        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 shadow-lg">
          <p class="text-xs uppercase tracking-wide text-emerald-400 mb-1">
            Próximo módulo
          </p>
          <p class="text-lg font-semibold text-slate-50">
            Gestión de Videos
          </p>
          <p class="mt-2 text-sm text-slate-400">
            Podrás crear, actualizar y eliminar videos de inducción.
          </p>
          <RouterLink
            :to="{ name: 'admin.videos' }"
            class="inline-flex items-center mt-4 text-xs font-semibold text-sky-400 hover:text-sky-300"
          >
            Ir al módulo de videos →
          </RouterLink>
        </div>

        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 shadow-lg">
          <p class="text-xs uppercase tracking-wide text-amber-400 mb-1">
            Seguimiento
          </p>
          <p class="text-lg font-semibold text-slate-50">
            Progreso de Personal
          </p>
          <p class="mt-2 text-sm text-slate-400">
            Más adelante mostraremos estadísticas de finalización y firmas.
          </p>
        </div>
      </div>

      <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 shadow-xl">
        <h2 class="text-lg font-semibold text-slate-50 mb-2">
          <ul class="text-sm text-slate-300 space-y-1">
            <li>Implementar módulo de carga de videos (MP4) desde este panel.</li>
            <li>Listar y ordenar los videos según el flujo del curso.</li>
            <li>
              <RouterLink
                :to="{ name: 'admin.declaracion' }"
                class="text-sky-400 hover:text-sky-300"
              >
                Configurar la declaración jurada (PDF) que firmarán los trabajadores.
              </RouterLink>
            </li>
          </ul>
        </h2>
        <ul class="list-disc list-inside text-sm text-slate-300 space-y-1">
          <li>Implementar módulo de carga de videos (MP4) desde este panel.</li>
          <li>Listar y ordenar los videos según el flujo del curso.</li>
          <li>Visualizar el avance de los trabajadores y sus declaraciones juradas.</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'
</script>
