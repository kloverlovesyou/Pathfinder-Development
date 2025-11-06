<template>
  <div class="flex flex-col items-center justify-center min-h-screen text-center">
    <h1 class="text-2xl font-bold mb-4">Checking Attendance...</h1>
    <p v-if="loading">Please wait while we record your attendance.</p>
    <p v-if="message" class="mt-4 text-lg font-semibold">{{ message }}</p>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useRoute } from "vue-router";

const route = useRoute();
const message = ref("");
const loading = ref(true);

onMounted(async () => {
  const trainingID = route.query.trainingID;
  const key = route.query.key;

  if (!trainingID || !key) {
    message.value = "❌ Invalid or incomplete QR Code.";
    loading.value = false;
    return;
  }

  try {
    const response = await axios.post(
      `${import.meta.env.VITE_API_BASE_URL}/attendance/checkin`,
      { trainingID, key },
      { withCredentials: true }
    );
    message.value = response.data.message;
  } catch (error) {
    if (error.response) {
      message.value = error.response.data.message || "❌ Attendance failed.";
    } else {
      message.value = "⚠️ Server unreachable.";
    }
  } finally {
    loading.value = false;
  }
});
</script>