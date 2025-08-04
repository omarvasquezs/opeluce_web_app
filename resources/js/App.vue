
<template>
  <router-view v-slot="{ Component, route }">
    <template v-if="route.path !== '/login'">
      <NavBar />
    </template>
    <component :is="Component" />
    <template v-if="route.path !== '/login'">
      <Footer />
    </template>
  </router-view>
</template>

<script setup>
import NavBar from './components/NavBar.vue';
import Footer from './components/Footer.vue';
</script>

<style>
/* Global styles for background consistency */
body {
  margin: 0;
  padding: 0;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  min-height: 100vh;
}

html {
  margin: 0;
  padding: 0;
}

#app {
  min-height: 100vh;
  background: transparent;
}

.container-fluid, .container {
  background: transparent;
}
</style>
