<template>
  <div class="flex flex-col items-center justify-center min-h-screen text-center px-4">
    <h1 class="text-2xl font-bold mb-4">Attendance Check-In</h1>

    <!-- Loading message -->
    <p v-if="loading">Checking QR code and loading training...</p>

    <!-- Invalid QR or fetch errors -->
    <p v-if="invalidQR" class="text-red-600 font-semibold">{{ invalidQR }}</p>

    <!-- Training info -->
    <div v-if="training && !loading" class="mb-4 p-4 border rounded w-full max-w-md bg-gray-50 text-left">
      <h2 class="text-xl font-semibold mb-1">{{ training.title }}</h2>
      <p class="mb-1">{{ training.description }}</p>
      <p class="text-sm text-gray-600 mb-1">
        <strong>Schedule:</strong> {{ formatToPHT(training.schedule) }} to {{formatToPHT(training.end_time) }}
      </p>
      <p class="text-sm text-gray-600 mb-1"><strong>Mode:</strong> {{ training.mode }}</p>
      <p class="text-sm text-gray-600 mb-1" v-if="training.location"><strong>Location:</strong> {{ training.location }}</p>
      <p class="text-sm text-gray-600 mb-1" v-if="training.trainingLink">
        <strong>Training Link:</strong> 
        <a :href="training.trainingLink" target="_blank" class="underline text-blue-600">{{ training.trainingLink }}</a>
      </p>
      <p class="text-sm text-gray-600">
        <strong>Organization:</strong> {{ training.organization?.name || 'Unknown' }}
      </p>
      <p class="text-sm text-gray-600 mt-1" v-if="training.attendance_link">
        <strong>Attendance Link:</strong> 
        <a :href="training.attendance_link" target="_blank" class="underline text-blue-600">{{ training.attendance_link }}</a>
      </p>
    </div>

    <!-- Attendance Form -->
    <form v-if="!loading && !submitted && !invalidQR" @submit.prevent="submitAttendance" class="space-y-3 w-full max-w-md">
      <p class="mb-2">Please enter your details to record attendance:</p>

      <input v-model="first_name" type="text" placeholder="First Name" class="w-full p-2 border rounded" required />
      <input v-model="last_name" type="text" placeholder="Last Name" class="w-full p-2 border rounded" required />
      <input v-model="email" type="email" placeholder="Email" class="w-full p-2 border rounded" required />
      <input v-model="phone" type="text" placeholder="Phone Number" class="w-full p-2 border rounded" required />

      <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700" :disabled="loading">
        Submit Attendance
      </button>
    </form>

    <!-- Success or error message -->
   <div v-if="submitted" :class="messageBoxClass" class="mt-4 w-full max-w-md p-3 rounded shadow flex items-center space-x-2">
      <span class="text-xl">{{ messageIcon }}</span>
      <span class="font-medium">{{ message }}</span>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import { useRoute } from "vue-router";

const messageBoxClass = computed(() =>
  submittedSuccess.value
    ? 'bg-green-100 text-green-800 border border-green-400'
    : 'bg-red-100 text-red-800 border border-red-400'
);

const messageIcon = computed(() =>
  submittedSuccess.value ? '✅' : '⚠️'
);

const route = useRoute();

const trainingID = ref(null);
const key = ref(null);
const training = ref(null);

const first_name = ref("");
const last_name = ref("");
const email = ref("");
const phone = ref("");

const message = ref("");
const loading = ref(true);
const invalidQR = ref("");
const submitted = ref(false);
const submittedSuccess = ref(false);

onMounted(async () => {
  // Read QR code query params
  trainingID.value = route.query.trainingID;
  key.value = route.query.key;

  console.log("TrainingID:", trainingID.value, "Key:", key.value);

  if (!trainingID.value || !key.value) {
    invalidQR.value = "❌ Invalid or incomplete QR code.";
    loading.value = false;
    return;
  }

  // Fetch training details
  try {
    const res = await axios.get(`${import.meta.env.VITE_API_BASE_URL}/training/${trainingID.value}`);
    console.log("Training Response:", res.data);
    training.value = res.data;

    // Optional: check if the QR key matches the fetched training's key
    if (training.value.attendance_key && training.value.attendance_key !== key.value) {
      invalidQR.value = "❌ QR code key does not match this training.";
    }

  } catch (error) {
    console.error(error);
    invalidQR.value = "❌ Failed to load training information. Please check the link or try again.";
  } finally {
    loading.value = false;
  }
});

function formatToPHT(dateStr) {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleString('en-PH', {
    timeZone: 'Asia/Manila',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    hour12: true,
  });
}

async function submitAttendance() {
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
      firstName: first_name.value,
      lastName: last_name.value,
      emailAddress: email.value,
      phoneNumber: phone.value,
    });

    message.value = res.data.message || "✅ Attendance Recorded Successfully";
    submittedSuccess.value = true;
    submitted.value = true;
  } catch (error) {
    console.error(error);
    message.value = error.response?.data?.message || "⚠️ Attendance submission failed.";
    submittedSuccess.value = false;
    submitted.value = true;
  }
}
</script>