<template>
  <div class="flex flex-col items-center justify-center min-h-screen text-center px-4">
    <h1 class="text-2xl font-bold mb-4">Attendance Check-In</h1>

    <!-- Loading message while QR params are being read -->
    <p v-if="loading">Checking QR code...</p>

    <!-- Invalid QR -->
    <p v-if="invalidQR" class="text-red-600 font-semibold">{{ invalidQR }}</p>

    <!-- Attendance Form -->
    <form v-if="!loading && !submitted && !invalidQR" @submit.prevent="submitAttendance" class="space-y-3 w-full max-w-md">
      <p class="mb-2">Please enter your details to record attendance:</p>

      <input v-model="first_name" type="text" placeholder="First Name" class="w-full p-2 border rounded" required />
      <input v-model="last_name" type="text" placeholder="Last Name" class="w-full p-2 border rounded" required />
      <input v-model="email" type="email" placeholder="Email" class="w-full p-2 border rounded" required />
      <input v-model="phone" type="text" placeholder="Phone Number" class="w-full p-2 border rounded" required />

      <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700">Submit Attendance</button>
    </form>

    <!-- Success or error message -->
    <p v-if="submitted" class="mt-4 text-lg font-semibold" :class="submittedSuccess ? 'text-green-600' : 'text-red-600'">
      {{ message }}
    </p>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useRoute } from "vue-router";

const route = useRoute();

const trainingID = ref(null);
const key = ref(null);

const first_name = ref("");
const last_name = ref("");
const email = ref("");
const phone = ref("");

const message = ref("");
const loading = ref(true);
const invalidQR = ref("");
const submitted = ref(false);
const submittedSuccess = ref(false);

onMounted(() => {
  // Read QR parameters from URL
  trainingID.value = route.query.trainingID;
  key.value = route.query.key;

  if (!trainingID.value || !key.value) {
    invalidQR.value = "❌ Invalid or incomplete QR code.";
  }

  loading.value = false;
});

async function submitAttendance() {
  // Validate frontend fields
  if (!first_name.value || !last_name.value || !email.value || !phone.value) {
    message.value = "⚠️ All fields are required.";
    submittedSuccess.value = false;
    submitted.value = true;
    return;
  }

  try {
    const res = await axios.post(`${import.meta.env.VITE_API_BASE_URL}/attendance/checkin`, {
      trainingID: trainingID.value,
      key: key.value,
      first_name: first_name.value.trim(),
      last_name: last_name.value.trim(),
      email: email.value.trim(),
      phone: phone.value.trim(),
    });

    message.value = res.data.message || "✅ Attendance Recorded Successfully";
    submittedSuccess.value = true;
    submitted.value = true;
  } catch (error) {
    message.value = error.response?.data?.message || "⚠️ Attendance submission failed.";
    submittedSuccess.value = false;
    submitted.value = true;
  }
}
</script>