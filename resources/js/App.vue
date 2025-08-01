
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
/* Global styles can go here */
</style>
