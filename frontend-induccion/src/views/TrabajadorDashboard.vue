<template>
  <div class="min-h-[calc(100vh-56px)] bg-slate-950">
    <div class="max-w-4xl mx-auto px-4 py-8">
      <h1 class="text-2xl font-semibold text-slate-50 mb-2">
        Bienvenido al Curso de Inducción
      </h1>
      <p class="text-sm text-slate-400 mb-6">
        Completa todos los videos en orden y firma tu declaración jurada al finalizar.
      </p>

      <div class="grid gap-4 mb-8">
        <div class="bg-slate-900/80 border border-sky-500/30 rounded-2xl p-5 shadow-lg">
          <p class="text-xs uppercase tracking-wide text-sky-400 mb-1">
            Estado del curso
          </p>
          <p class="text-sm text-slate-300">
            Próximamente aquí verás tu avance: videos completados, pendientes y acceso a la declaración jurada.
          </p>
        </div>

        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 shadow-lg">
          <p class="text-xs uppercase tracking-wide text-emerald-400 mb-1">
            Siguiente paso
          </p>
          <p class="text-sm text-slate-300">
            En el siguiente módulo activaremos la lista de videos del curso y el reproductor con control de avance.
          </p>
        </div>
      </div>

      <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 shadow-xl">
        <h2 class="text-lg font-semibold text-slate-50 mb-2">
          Recomendaciones
        </h2>
        <ul class="list-disc list-inside text-sm text-slate-300 space-y-1">
          <li>Realiza la inducción en un entorno tranquilo y sin interrupciones.</li>
          <li>No cierres la ventana del navegador mientras estés viendo un video.</li>
          <li>Al finalizar todos los videos, podrás firmar tu declaración jurada en línea.</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
</script>
