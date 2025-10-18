<script setup>
import { useRoute } from "vue-router";

const route = useRoute();

const noHeaderRoutes = [
  "/loginform",
  "/typeofaccount",
  "/a_registrationform",
  "/o_registrationform",
  "/OrganizationHomePage",
  "/OrgTrainings",
  "/OrgCareers",
  "/updateprofile",
  "/OrgCalendar",
];
</script>

<template>
  <router-view />
</template>
