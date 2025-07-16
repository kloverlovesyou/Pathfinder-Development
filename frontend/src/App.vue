<script setup>
import { useRoute } from "vue-router";
import Header from "./components/Layout/Header.vue";
import Footer from "./components/Layout/Footer.vue";

// Access the current route
const route = useRoute();
</script>

<template>
  <div class="min-h-screen flex flex-col justify-between">
    <Header v-if="route.path !== '/loginform'" />

    <main class="flex-grow">
      <router-view />
    </main>
    <Footer v-if="route.path !== '/loginform'" />
  </div>
</template>

<style scoped></style>