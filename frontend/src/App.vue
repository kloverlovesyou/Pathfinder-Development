<script setup>
import { useRoute } from "vue-router";
import Header from "./components/Layout/Header.vue";
import Footer from "./components/Layout/Footer.vue";
import TypeOfAccount from "./components/Home/TypeOfAccount.vue";
import LoginForm from "./components/Home/LoginForm.vue";

// Access the current route
const route = useRoute();
</script>

<template>
  <div class="min-h-screen flex flex-col justify-between">
    <Header
      v-if="
        route.path !== '/loginform' &&
        route.path !== '/typeofaccount' &&
        route.path !== '/a_registrationform' &&
        route.path !== '/o_registrationform'
      "
    />
    <main class="flex-grow">
      <router-view></router-view>
    </main>
    <Footer
      v-if="
        route.path !== '/loginform' &&
        route.path !== '/typeofaccount' &&
        route.path !== '/a_registrationform' &&
        route.path !== '/o_registrationform'
      "
    />
  </div>
</template>

<style scoped></style>
